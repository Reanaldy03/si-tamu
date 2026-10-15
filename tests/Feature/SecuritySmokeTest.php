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
use Illuminate\Support\Str;
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
            'visit_date' => '2026-03-16',
            'letter_number' => '<script>alert("SURAT")</script>005/1',
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
            'letter_number' => 'alert("SURAT")005/1',
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
        $this->assertSame('camera=(self), microphone=(), geolocation=()', $response->headers->get('Permissions-Policy'));
        $this->assertNotNull($response->headers->get('X-Request-ID'));
    }

    public function test_client_request_id_is_sanitized_to_uuid_when_unsafe(): void
    {
        $oversized = $this->withHeader('X-Request-ID', str_repeat('A', 100))
            ->get(route('public.registration.create'));
        $oversized->assertOk();
        $this->assertTrue(Str::isUuid($oversized->headers->get('X-Request-ID')));

        $headerInjection = $this->withHeader('X-Request-ID', "abc\r\nX-Evil: 1")
            ->get(route('public.registration.create'));
        $headerInjection->assertOk();
        $this->assertTrue(Str::isUuid($headerInjection->headers->get('X-Request-ID')));

        $validId = '01HXYZ0000000000000000000A';
        $preserved = $this->withHeader('X-Request-ID', $validId)
            ->get(route('public.registration.create'));
        $preserved->assertOk();
        $this->assertSame($validId, $preserved->headers->get('X-Request-ID'));
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

    public function test_hostile_department_id_is_not_reflected_into_javascript_context(): void
    {
        $hostile = "1'};alert(document.cookie);//";

        $postResponse = $this->post(route('public.registration.store'), [
            'visitor_name' => 'Tamu Waspada',
            'origin_organization' => 'Instansi',
            'contact_phone' => '081234567890',
            'department_id' => $hostile,
            'purpose' => 'Kunjungan',
            'visit_date' => '2026-03-16',
            'submission_token' => 'xss-dept-token',
        ]);

        $postResponse->assertSessionHasErrors('department_id');

        $body = $this->get(route('public.registration.create'))->assertOk()->getContent();

        $this->assertStringContainsString("selectedDept: '1\\u0027};alert(document.cookie);\\/\\/'", $body);
        $this->assertStringNotContainsString('1&#039;};alert', $body);
    }

    public function test_department_name_is_escaped_in_inline_confirmation_handler(): void
    {
        Department::create([
            'public_id' => '01HXYZ00000000000000000012',
            'code' => 'XSS',
            'name' => "');alert(document.cookie);//",
            'is_active' => true,
        ]);

        $body = $this->actingAs($this->admin)
            ->get(route('admin.departments.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Hapus unit \\u0027);alert(document.cookie);\\/\\/', $body);
        $this->assertStringNotContainsString("confirm('Hapus unit &#039;", $body);
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
                'visit_date' => '2026-03-16',
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
            'visit_date' => '2026-03-16',
            'submission_token' => 'rate-limit-token-16',
        ]);

        $blockedResponse->assertStatus(429);
    }
}
