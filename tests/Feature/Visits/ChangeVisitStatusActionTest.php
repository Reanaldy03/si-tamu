<?php

namespace Tests\Feature\Visits;

use App\Application\Visits\Actions\ChangeVisitStatusAction;
use App\Application\Visits\DTOs\ChangeVisitStatusDTO;
use App\Domain\Visits\Enums\RegistrationSource;
use App\Domain\Visits\Enums\VisitStatus;
use App\Domain\Visits\Exceptions\InvalidVisitStatusTransitionException;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitStatusHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ChangeVisitStatusActionTest extends TestCase
{
    use RefreshDatabase;

    protected ChangeVisitStatusAction $action;

    protected Visit $visit;

    protected User $staffUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->action = $this->app->make(ChangeVisitStatusAction::class);

        $dept = Department::create([
            'public_id' => (string) Str::ulid(),
            'code' => 'KEU',
            'name' => 'Bagian Keuangan',
            'is_active' => true,
        ]);

        $this->staffUser = User::create([
            'public_id' => (string) Str::ulid(),
            'name' => 'Petugas Front Office',
            'email' => 'staff@example.com',
            'password' => 'secret123',
            'is_active' => true,
        ]);

        $now = now();

        $this->visit = Visit::create([
            'public_id' => (string) Str::ulid(),
            'visitor_name' => 'Hendro',
            'origin_organization' => 'Bank Mandiri',
            'party_size' => 1,
            'department_id' => $dept->id,
            'department_name_snapshot' => $dept->name,
            'purpose' => 'Rekonsiliasi Rekening',
            'current_status' => VisitStatus::REGISTERED,
            'registration_source' => RegistrationSource::PUBLIC,
            'registered_at' => $now,
            'status_updated_at' => $now,
        ]);

        VisitStatusHistory::create([
            'visit_id' => $this->visit->id,
            'from_status' => null,
            'to_status' => VisitStatus::REGISTERED,
            'source' => 'PUBLIC',
            'changed_at' => $now,
        ]);
    }

    public function test_valid_sequential_transitions(): void
    {
        // 1. REGISTERED -> ARRIVED
        $dtoArrive = new ChangeVisitStatusDTO(
            visitId: $this->visit->id,
            toStatus: VisitStatus::ARRIVED,
            changedByUserId: $this->staffUser->id,
            source: 'STAFF',
            reason: 'Tamu tiba di meja resepsionis',
            requestId: 'req-002',
            ipAddress: '192.168.1.50',
        );

        $arrivedVisit = $this->action->execute($dtoArrive);
        $this->assertSame(VisitStatus::ARRIVED, $arrivedVisit->current_status);

        $this->assertDatabaseHas('visit_status_history', [
            'visit_id' => $this->visit->id,
            'from_status' => 'REGISTERED',
            'to_status' => 'ARRIVED',
            'changed_by_user_id' => $this->staffUser->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'visit.status_changed',
            'target_id' => $this->visit->id,
            'actor_user_id' => $this->staffUser->id,
        ]);

        // 2. ARRIVED -> IN_SERVICE
        $dtoService = new ChangeVisitStatusDTO(
            visitId: $this->visit->id,
            toStatus: VisitStatus::IN_SERVICE,
            changedByUserId: $this->staffUser->id,
            reason: 'Tamu memasuki ruang rapat',
        );
        $serviceVisit = $this->action->execute($dtoService);
        $this->assertSame(VisitStatus::IN_SERVICE, $serviceVisit->current_status);

        // 3. IN_SERVICE -> COMPLETED
        $dtoComplete = new ChangeVisitStatusDTO(
            visitId: $this->visit->id,
            toStatus: VisitStatus::COMPLETED,
            changedByUserId: $this->staffUser->id,
            reason: 'Layanan selesai',
        );
        $completedVisit = $this->action->execute($dtoComplete);
        $this->assertSame(VisitStatus::COMPLETED, $completedVisit->current_status);

        $this->assertCount(4, $this->visit->fresh()->statusHistories);
    }

    public function test_cancellation_from_registered_status(): void
    {
        $dtoCancel = new ChangeVisitStatusDTO(
            visitId: $this->visit->id,
            toStatus: VisitStatus::CANCELLED,
            changedByUserId: $this->staffUser->id,
            reason: 'Tamu membatalkan kedatangan karena urusan mendadak',
        );

        $cancelledVisit = $this->action->execute($dtoCancel);
        $this->assertSame(VisitStatus::CANCELLED, $cancelledVisit->current_status);

        $this->assertDatabaseHas('visit_status_history', [
            'visit_id' => $this->visit->id,
            'from_status' => 'REGISTERED',
            'to_status' => 'CANCELLED',
            'reason' => 'Tamu membatalkan kedatangan karena urusan mendadak',
        ]);
    }

    public function test_illegal_status_transition_rolls_back_and_leaves_state_intact(): void
    {
        $initialHistoryCount = VisitStatusHistory::count();
        $initialAuditCount = AuditLog::count();

        // Illegal transition: REGISTERED directly to COMPLETED
        $dtoIllegal = new ChangeVisitStatusDTO(
            visitId: $this->visit->id,
            toStatus: VisitStatus::COMPLETED,
            changedByUserId: $this->staffUser->id,
        );

        try {
            $this->action->execute($dtoIllegal);
            $this->fail('Seharusnya melempar InvalidVisitStatusTransitionException.');
        } catch (InvalidVisitStatusTransitionException $e) {
            $this->assertSame(VisitStatus::REGISTERED, $e->fromStatus);
            $this->assertSame(VisitStatus::COMPLETED, $e->toStatus);
        }

        // Verify state is completely intact
        $this->visit->refresh();
        $this->assertSame(VisitStatus::REGISTERED, $this->visit->current_status);
        $this->assertSame($initialHistoryCount, VisitStatusHistory::count());
        $this->assertSame($initialAuditCount, AuditLog::count());
    }
}
