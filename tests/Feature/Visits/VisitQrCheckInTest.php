<?php

namespace Tests\Feature\Visits;

use App\Application\Visits\Actions\RegisterVisitAction;
use App\Application\Visits\DTOs\RegisterVisitDTO;
use App\Application\Visits\Services\VisitQrTokenService;
use App\Domain\Access\Enums\RoleCode;
use App\Domain\Visits\Enums\RegistrationSource;
use App\Domain\Visits\Enums\VisitStatus;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\DepartmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitQrCheckInTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected Department $department;

    protected VisitQrTokenService $qrTokenService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->seed(DepartmentSeeder::class);

        $this->department = Department::first();

        // Create receptionist staff user
        $staffRole = Role::where('slug', RoleCode::STAFF->value)->first();
        $this->staff = User::factory()->create(['is_active' => true]);
        $this->staff->roles()->attach($staffRole->id, [
            'assigned_at' => now(),
        ]);

        $this->qrTokenService = app(VisitQrTokenService::class);
    }

    public function test_guest_registration_generates_cryptographic_qr_token(): void
    {
        $dto = new RegisterVisitDTO(
            visitorName: 'Ahmad Siregar',
            originOrganization: 'Dinas Kominfo Binjai',
            departmentId: $this->department->id,
            purpose: 'Koordinasi sistem informasi',
            contactPhone: '081234567890',
            partySize: 2,
            registrationSource: RegistrationSource::PUBLIC,
        );

        $action = app(RegisterVisitAction::class);
        $visit = $action->execute($dto);

        $this->assertNotNull($visit->qr_token);
        $this->assertEquals(64, strlen($visit->qr_token));

        // Verify token can be validated
        $rawToken = $this->qrTokenService->generateRawToken($visit->public_id);
        $this->assertEquals($visit->qr_token, $this->qrTokenService->hashToken($rawToken));
    }

    public function test_guest_can_view_registration_success_with_qr_code_and_printable_ticket(): void
    {
        $visit = $this->createRegisteredVisit();

        // Success page
        $response = $this->get(route('public.registration.success', ['publicId' => $visit->public_id]));
        $response->assertOk();
        $response->assertSee('QR Code Check-In Tamu');
        $response->assertSee($visit->public_id);

        // Printable Ticket — carries the institution's official kop surat.
        // The four letterhead lines are set in sentence case and uppercased by
        // CSS, so assert the DOM text, not the rendered casing.
        $ticketResponse = $this->get(route('public.registration.ticket', ['publicId' => $visit->public_id]));
        $ticketResponse->assertOk();
        $ticketResponse->assertSee('Pemerintah Kota Binjai');
        $ticketResponse->assertSee('Sekretariat Dewan Perwakilan Rakyat Daerah');
        $ticketResponse->assertSee('Jalan Veteran');
        $ticketResponse->assertSee('Binjai - 20714');
        $ticketResponse->assertSee($visit->visitor_name);
        $ticketResponse->assertSee($visit->department_name_snapshot);
    }

    public function test_receptionist_can_check_in_guest_via_valid_qr_payload(): void
    {
        $visit = $this->createRegisteredVisit();
        $qrPayload = $this->qrTokenService->generateQrPayload($visit);

        $this->assertNull($visit->checked_in_at);
        $this->assertEquals(VisitStatus::REGISTERED, $visit->current_status);

        $response = $this->actingAs($this->staff)
            ->postJson(route('admin.checkin.process'), [
                'payload' => $qrPayload,
            ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.visitor_name', $visit->visitor_name);
        $response->assertJsonPath('data.current_status', VisitStatus::ARRIVED->label());
        $response->assertJsonPath('data.checked_in_at', now()->timezone('Asia/Jakarta')->format('H:i').' WIB');

        $freshVisit = $visit->fresh();
        $this->assertEquals(VisitStatus::ARRIVED, $freshVisit->current_status);
        $this->assertNotNull($freshVisit->checked_in_at);

        // Verify status history
        $this->assertDatabaseHas('visit_status_history', [
            'visit_id' => $visit->id,
            'from_status' => VisitStatus::REGISTERED->value,
            'to_status' => VisitStatus::ARRIVED->value,
            'changed_by_user_id' => $this->staff->id,
            'source' => 'STAFF',
        ]);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'visit.checked_in_qr',
            'actor_user_id' => $this->staff->id,
            'target_id' => $visit->id,
        ]);
    }

    public function test_receptionist_can_check_in_guest_via_fallback_manual_ulid(): void
    {
        $visit = $this->createRegisteredVisit();

        $response = $this->actingAs($this->staff)
            ->postJson(route('admin.checkin.process'), [
                'payload' => $visit->public_id,
            ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $this->assertEquals(VisitStatus::ARRIVED, $visit->fresh()->current_status);
    }

    public function test_check_in_fails_with_invalid_or_forged_qr_token(): void
    {
        $visit = $this->createRegisteredVisit();

        // Forged payload with random fake token
        $forgedPayload = json_encode([
            'app' => 'SI-TAMU',
            'public_id' => $visit->public_id,
            'token' => 'fake-unauthorized-random-token-1234567890',
        ]);

        $response = $this->actingAs($this->staff)
            ->postJson(route('admin.checkin.process'), [
                'payload' => $forgedPayload,
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('message', 'QR Code tidak valid atau telah dimanipulasi.');

        // State remains intact
        $this->assertEquals(VisitStatus::REGISTERED, $visit->fresh()->current_status);
    }

    public function test_duplicate_check_in_is_rejected_if_already_arrived(): void
    {
        $visit = $this->createRegisteredVisit();
        $qrPayload = $this->qrTokenService->generateQrPayload($visit);

        // First check in succeeds
        $this->actingAs($this->staff)->postJson(route('admin.checkin.process'), ['payload' => $qrPayload])->assertOk();

        // Second check in attempt
        $secondResponse = $this->actingAs($this->staff)
            ->postJson(route('admin.checkin.process'), [
                'payload' => $qrPayload,
            ]);

        $secondResponse->assertStatus(422);
        $secondResponse->assertJsonPath('success', false);
        $this->assertStringContainsString('sudah melakukan check-in', $secondResponse->json('message'));
    }

    public function test_unauthenticated_user_cannot_access_scanner_or_process_check_in(): void
    {
        $this->get(route('admin.checkin.scan'))->assertRedirect(route('login'));

        $this->postJson(route('admin.checkin.process'), ['payload' => 'dummy'])
            ->assertStatus(401);
    }

    public function test_user_without_visit_permission_cannot_check_in(): void
    {
        $userWithoutRole = User::factory()->create(['is_active' => true]);

        $this->actingAs($userWithoutRole)
            ->get(route('admin.checkin.scan'))
            ->assertForbidden();

        $this->actingAs($userWithoutRole)
            ->postJson(route('admin.checkin.process'), ['payload' => 'dummy'])
            ->assertForbidden();

        // Navigation and dashboard must not advertise the scanner to unauthorized users.
        $dashboard = $this->actingAs($userWithoutRole)->get(route('admin.dashboard'));
        $dashboard->assertOk();
        $dashboard->assertDontSee(route('admin.checkin.scan'), false);

        // Authorized staff still see it.
        $staffDashboard = $this->actingAs($this->staff)->get(route('admin.dashboard'));
        $staffDashboard->assertOk();
        $staffDashboard->assertSee(route('admin.checkin.scan'), false);
    }

    protected function createRegisteredVisit(): Visit
    {
        $dto = new RegisterVisitDTO(
            visitorName: 'Bambang Sudibyo',
            originOrganization: 'Kantor Bappeda',
            departmentId: $this->department->id,
            purpose: 'Konsultasi APBD',
            contactPhone: '081299998888',
            partySize: 1,
            registrationSource: RegistrationSource::PUBLIC,
        );

        return app(RegisterVisitAction::class)->execute($dto);
    }
}
