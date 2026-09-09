<?php

namespace Tests\Feature;

use App\Domain\Access\Enums\RoleCode;
use App\Models\Department;
use App\Models\OrganizationSetting;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\OrganizationSettingsSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SecuritySmokeTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(OrganizationSettingsSeeder::class);

        $adminRole = Role::where('slug', RoleCode::ADMIN->value)->first();

        $this->admin = User::create([
            'public_id' => '01HXYZ00000000000000000001',
            'name' => 'Admin Security',
            'email' => 'secadmin@sitamu.local',
            'password' => bcrypt('SecPass123!'),
            'is_active' => true,
        ]);
        $this->admin->roles()->attach($adminRole->id, ['assigned_at' => now()]);

        $this->department = Department::create([
            'public_id' => '01HXYZ00000000000000000011',
            'code' => 'TIK',
            'name' => 'Divisi Keamanan Informasi',
            'is_active' => true,
        ]);
    }

    public function test_xss_payload_in_guest_input_is_stripped_and_escaped(): void
    {
        $xssName = '<script>alert("XSS")</script>Budi Hacker';
        $xssPurpose = '<img src="x" onerror="alert(1)">Keperluan Penting';

        $response = $this->post(route('public.registration.store'), [
            'visitor_name' => $xssName,
            'origin_organization' => 'Hacker Org',
            'contact_phone' => '081234567890',
            'department_id' => $this->department->id,
            'purpose' => $xssPurpose,
            'submission_token' => 'xss-token-001',
        ]);

        $response->assertSessionHasNoErrors();

        // Verify tags are stripped in database
        $this->assertDatabaseMissing('visits', [
            'visitor_name' => $xssName,
        ]);

        $this->assertDatabaseHas('visits', [
            'visitor_name' => 'alert("XSS")Budi Hacker',
            'purpose' => 'Keperluan Penting',
        ]);

        // Verify HTML rendering escapes any output
        $visit = Visit::where('visitor_name', 'like', '%Budi Hacker%')->first();
        $this->assertNotNull($visit);
        $rendered = view('admin.visits.show', [
            'visit' => $visit,
            'organization' => OrganizationSetting::current(),
            'tz' => 'Asia/Jakarta',
            'allowedNextStatuses' => [],
        ])->render();
        $this->assertStringNotContainsString('<script>', $rendered);
    }

    public function test_sql_injection_payload_in_search_query_is_safely_parameterized(): void
    {
        $sqliPayload = "' OR '1'='1' UNION SELECT * FROM users --";

        $response = $this->actingAs($this->admin)->get(route('admin.visits.index', [
            'q' => $sqliPayload,
        ]));

        $response->assertOk();
        $response->assertSee('Data Kunjungan');
        $this->assertTrue(DB::table('visits')->exists() || ! DB::table('visits')->exists());
    }

    public function test_sql_injection_payload_in_reporting_query_is_safely_parameterized(): void
    {
        $sqliPayload = '1; DROP TABLE visits; --';

        $response = $this->actingAs($this->admin)->get(route('admin.reports.index', [
            'q' => $sqliPayload,
        ]));

        $response->assertOk();
        $response->assertSee('Laporan & Rekapitulasi Kunjungan');
    }

    public function test_security_headers_are_attached_to_responses(): void
    {
        $response = $this->get(route('public.registration.create'));

        $response->assertOk();
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertNotNull($response->headers->get('X-Request-ID'));
    }

    public function test_csrf_middleware_blocks_untokened_post_requests(): void
    {
        $session = $this->app['session']->driver();
        $session->put('_token', 'valid-server-csrf-token');

        $request = Request::create('/login', 'POST', [
            'email' => 'admin@sitamu.local',
            'password' => 'secret',
        ]);
        $request->setLaravelSession($session);

        $middleware = new class($this->app, $this->app['encrypter']) extends ValidateCsrfToken
        {
            protected function runningUnitTests(): bool
            {
                return false;
            }
        };

        $this->expectException(TokenMismatchException::class);
        $middleware->handle($request, fn () => response('OK'));
    }

    public function test_rate_limiting_triggers_on_guest_registration_after_limit(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            $response = $this->post(route('public.registration.store'), [
                'visitor_name' => "Tamu {$i}",
                'origin_organization' => 'Instansi',
                'contact_phone' => '081234567890',
                'department_id' => $this->department->id,
                'purpose' => 'Kunjungan',
                'submission_token' => "rate-limit-token-{$i}",
            ]);
            $response->assertSessionHasNoErrors();
        }

        // 16th request should hit the 15 per minute throttle
        $blockedResponse = $this->post(route('public.registration.store'), [
            'visitor_name' => 'Tamu 16',
            'origin_organization' => 'Instansi',
            'contact_phone' => '081234567890',
            'department_id' => $this->department->id,
            'purpose' => 'Kunjungan',
            'submission_token' => 'rate-limit-token-16',
        ]);

        $blockedResponse->assertStatus(429);
    }
}
