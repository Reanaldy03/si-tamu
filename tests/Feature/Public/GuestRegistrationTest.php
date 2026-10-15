<?php

namespace Tests\Feature\Public;

use App\Domain\Visits\Enums\RegistrationSource;
use App\Domain\Visits\Enums\VisitStatus;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\OrganizationSetting;
use App\Models\Visit;
use Database\Seeders\OrganizationSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(OrganizationSettingsSeeder::class);

        OrganizationSetting::where('id', 1)->update([
            'organization_name' => 'Kantor Layanan Terpadu',
            'registration_instruction' => 'Silakan isi data kunjungan Anda.',
            'privacy_notice' => 'Data Anda dilindungi untuk keperluan administrasi.',
            'timezone' => 'Asia/Jakarta',
        ]);
    }

    public function test_guest_can_view_registration_form_with_active_departments(): void
    {
        $activeDept = Department::create([
            'public_id' => '01HXYZ00000000000000000001',
            'code' => 'UMUM',
            'name' => 'Bagian Umum dan Protokol',
            'description' => 'Layanan administrasi umum',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $inactiveDept = Department::create([
            'public_id' => '01HXYZ00000000000000000002',
            'code' => 'ARSIP',
            'name' => 'Bagian Kearsipan Khusus',
            'description' => 'Sedang tutup sementara',
            'sort_order' => 2,
            'is_active' => false,
        ]);

        $response = $this->get(route('public.registration.create'));

        $response->assertOk();
        $response->assertSee('Kantor Layanan Terpadu');
        $response->assertSee('Bagian Umum dan Protokol');
        $response->assertDontSee('Bagian Kearsipan Khusus');
        $response->assertSee('submission_token');
    }

    public function test_guest_can_register_visit_successfully(): void
    {
        $department = Department::create([
            'public_id' => '01HXYZ00000000000000000001',
            'code' => 'SEKRETARIAT',
            'name' => 'Sekretariat Utama',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $token = 'unique-sub-token-12345';

        $payload = [
            'visitor_name' => 'Ahmad Dahlan',
            'origin_organization' => 'Universitas Indonesia',
            'department_id' => $department->id,
            'purpose' => 'Koordinasi penelitian dan pengabdian masyarakat',
            'visit_date' => '2026-03-16',
            'letter_number' => '005/123/DPRD/2026',
            'contact_phone' => '081234567890',
            'visitor_email' => 'ahmad@ui.ac.id',
            'party_size' => 3,
            'notes' => 'Membawa proposal',
            'submission_token' => $token,
        ];

        $response = $this->post(route('public.registration.store'), $payload);

        $visit = Visit::first();
        $this->assertNotNull($visit);
        $this->assertEquals('Ahmad Dahlan', $visit->visitor_name);
        $this->assertEquals('Universitas Indonesia', $visit->origin_organization);
        $this->assertEquals('Sekretariat Utama', $visit->department_name_snapshot);
        $this->assertEquals(3, $visit->party_size);
        $this->assertEquals('2026-03-16', $visit->visit_date->toDateString());
        $this->assertEquals('005/123/DPRD/2026', $visit->letter_number);
        $this->assertEquals(VisitStatus::REGISTERED, $visit->current_status);
        $this->assertEquals(RegistrationSource::PUBLIC, $visit->registration_source);

        $response->assertRedirect(route('public.registration.success', ['publicId' => $visit->public_id]));

        // Check history and audit
        $this->assertDatabaseHas('visit_status_history', [
            'visit_id' => $visit->id,
            'to_status' => VisitStatus::REGISTERED->value,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'visit.registered',
            'target_id' => $visit->id,
            'actor_type' => 'PUBLIC',
        ]);

        $auditState = AuditLog::where('target_id', $visit->id)
            ->where('event_type', 'visit.registered')
            ->firstOrFail()
            ->after_state;

        $this->assertSame('2026-03-16', $auditState['visit_date']);
        $this->assertSame('005/123/DPRD/2026', $auditState['letter_number']);

        // Visit success page
        $successResponse = $this->get(route('public.registration.success', ['publicId' => $visit->public_id]));
        $successResponse->assertOk();
        $successResponse->assertSee($visit->public_id);
        $successResponse->assertSee('Ahmad Dahlan');
        $successResponse->assertSee('Sekretariat Utama');
        $successResponse->assertSee('Registrasi Berhasil');
        $successResponse->assertSee('Tanggal Kunjungan');
        $successResponse->assertSee('16 Mar 2026');
        $successResponse->assertSee('Nomor Surat');
        $successResponse->assertSee('005/123/DPRD/2026');
    }

    public function test_registration_fails_when_required_fields_are_missing(): void
    {
        $response = $this->from(route('public.registration.create'))
            ->post(route('public.registration.store'), []);

        $response->assertRedirect(route('public.registration.create'));
        $response->assertSessionHasErrors(['visitor_name', 'origin_organization', 'department_id', 'purpose', 'visit_date']);
        $this->assertEquals(0, Visit::count());
    }

    public function test_registration_fails_when_department_is_inactive(): void
    {
        $inactiveDept = Department::create([
            'public_id' => '01HXYZ00000000000000000003',
            'code' => 'CLOSED',
            'name' => 'Departemen Nonaktif',
            'is_active' => false,
        ]);

        $response = $this->from(route('public.registration.create'))
            ->post(route('public.registration.store'), [
                'visitor_name' => 'Siti Nurhaliza',
                'origin_organization' => 'Kementerian',
                'department_id' => $inactiveDept->id,
                'purpose' => 'Konsultasi',
                'visit_date' => '2026-03-16',
            ]);

        $response->assertRedirect(route('public.registration.create'));
        $response->assertSessionHasErrors(['department_id']);
        $this->assertEquals(0, Visit::count());
    }

    public function test_registration_is_rejected_when_honeypot_is_filled(): void
    {
        $department = Department::create([
            'public_id' => '01HXYZ00000000000000000001',
            'code' => 'SEKRETARIAT',
            'name' => 'Sekretariat Utama',
            'is_active' => true,
        ]);

        $response = $this->from(route('public.registration.create'))
            ->post(route('public.registration.store'), [
                'visitor_name' => 'Spam Bot',
                'origin_organization' => 'Spam Org',
                'department_id' => $department->id,
                'purpose' => 'Buy cheap meds',
                'visit_date' => '2026-03-16',
                'website_hp' => 'http://spam-link.example.com',
            ]);

        $response->assertRedirect(route('public.registration.create'));
        $response->assertSessionHasErrors(['website_hp']);
        $this->assertEquals(0, Visit::count());
    }

    public function test_duplicate_submission_token_returns_existing_visit_idempotently(): void
    {
        $department = Department::create([
            'public_id' => '01HXYZ00000000000000000001',
            'code' => 'SEKRETARIAT',
            'name' => 'Sekretariat Utama',
            'is_active' => true,
        ]);

        $token = 'idempotent-token-999';

        $payload = [
            'visitor_name' => 'Joko Anwar',
            'origin_organization' => 'Sinema Indonesia',
            'department_id' => $department->id,
            'purpose' => 'Diskusi produksi film',
            'visit_date' => '2026-03-16',
            'submission_token' => $token,
        ];

        // First submission
        $res1 = $this->post(route('public.registration.store'), $payload);
        $this->assertEquals(1, Visit::count());
        $visit = Visit::first();
        $res1->assertRedirect(route('public.registration.success', ['publicId' => $visit->public_id]));

        // Second submission with exact same token
        $res2 = $this->post(route('public.registration.store'), $payload);
        $this->assertEquals(1, Visit::count()); // No duplicate row created!
        $res2->assertRedirect(route('public.registration.success', ['publicId' => $visit->public_id]));
    }
}
