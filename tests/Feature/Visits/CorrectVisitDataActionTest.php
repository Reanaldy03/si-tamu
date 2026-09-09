<?php

namespace Tests\Feature\Visits;

use App\Application\Visits\Actions\CorrectVisitDataAction;
use App\Application\Visits\DTOs\CorrectVisitDataDTO;
use App\Domain\Visits\Enums\RegistrationSource;
use App\Domain\Visits\Enums\VisitStatus;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CorrectVisitDataActionTest extends TestCase
{
    use RefreshDatabase;

    protected CorrectVisitDataAction $action;

    protected Visit $visit;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->action = $this->app->make(CorrectVisitDataAction::class);

        $dept = Department::create([
            'public_id' => (string) Str::ulid(),
            'code' => 'HUKUM',
            'name' => 'Bagian Hukum',
            'is_active' => true,
        ]);

        $this->adminUser = User::create([
            'public_id' => (string) Str::ulid(),
            'name' => 'Admin Koreksi',
            'email' => 'admin_correct@example.com',
            'password' => 'secret123',
            'is_active' => true,
        ]);

        $this->visit = Visit::create([
            'public_id' => (string) Str::ulid(),
            'visitor_name' => 'Ahmad Salah Nama',
            'origin_organization' => 'CV Sumber Makmur',
            'purpose' => 'Konsultasi Perizinan',
            'party_size' => 1,
            'department_id' => $dept->id,
            'department_name_snapshot' => $dept->name,
            'current_status' => VisitStatus::REGISTERED,
            'registration_source' => RegistrationSource::PUBLIC,
            'registered_at' => now(),
            'status_updated_at' => now(),
        ]);
    }

    public function test_correct_visit_data_updates_allowed_fields_and_records_audit_diff(): void
    {
        $dto = new CorrectVisitDataDTO(
            visitId: $this->visit->id,
            actorUserId: $this->adminUser->id,
            correctedAttributes: [
                'visitor_name' => 'Ahmad Dahlan (Koreksi)',
                'party_size' => 3,
                'public_id' => 'ILLEGAL_OVERWRITE', // Non-allowlisted field!
            ],
            reason: 'Koreksi ejaan nama pengunjung dan jumlah rombongan',
            requestId: 'req-correct-1',
            ipAddress: '10.0.0.1',
        );

        $updated = $this->action->execute($dto);

        $this->assertSame('Ahmad Dahlan (Koreksi)', $updated->visitor_name);
        $this->assertSame(3, $updated->party_size);
        $this->assertNotSame('ILLEGAL_OVERWRITE', $updated->public_id);

        // Verify audit log has before/after state diff
        $auditLog = AuditLog::where('event_type', 'visit.data_corrected')->latest('occurred_at')->first();
        $this->assertNotNull($auditLog);
        $this->assertSame($this->adminUser->id, $auditLog->actor_user_id);
        $this->assertSame($this->visit->id, $auditLog->target_id);
        $this->assertSame('Ahmad Salah Nama', $auditLog->before_state['visitor_name']);
        $this->assertSame('Ahmad Dahlan (Koreksi)', $auditLog->after_state['visitor_name']);
        $this->assertSame(1, $auditLog->before_state['party_size']);
        $this->assertSame(3, $auditLog->after_state['party_size']);
        $this->assertArrayNotHasKey('public_id', $auditLog->after_state);
    }

    public function test_no_audit_recorded_if_attributes_have_no_changes(): void
    {
        $dto = new CorrectVisitDataDTO(
            visitId: $this->visit->id,
            actorUserId: $this->adminUser->id,
            correctedAttributes: [
                'visitor_name' => 'Ahmad Salah Nama', // Same value as existing
            ],
            reason: 'Percobaan koreksi tanpa perubahan',
        );

        $initialAuditCount = AuditLog::count();
        $this->action->execute($dto);

        $this->assertSame($initialAuditCount, AuditLog::count());
    }
}
