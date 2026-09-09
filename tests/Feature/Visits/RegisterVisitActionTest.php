<?php

namespace Tests\Feature\Visits;

use App\Application\Audit\Actions\RecordAuditEventAction;
use App\Application\Visits\Actions\RegisterVisitAction;
use App\Application\Visits\DTOs\RegisterVisitDTO;
use App\Domain\Visits\Enums\RegistrationSource;
use App\Domain\Visits\Enums\VisitStatus;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Visit;
use App\Models\VisitStatusHistory;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RegisterVisitActionTest extends TestCase
{
    use RefreshDatabase;

    protected RegisterVisitAction $action;

    protected Department $activeDept;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->action = $this->app->make(RegisterVisitAction::class);

        $this->activeDept = Department::create([
            'public_id' => (string) Str::ulid(),
            'code' => 'UMUM',
            'name' => 'Bagian Umum',
            'is_active' => true,
        ]);
    }

    public function test_register_visit_creates_visit_history_and_audit_atomically(): void
    {
        $dto = new RegisterVisitDTO(
            visitorName: 'Rian Pratama',
            originOrganization: 'Dinas Kominfo',
            departmentId: $this->activeDept->id,
            purpose: 'Konsultasi Integrasi Sistem',
            contactPhone: '081234567890',
            partySize: 2,
            registrationSource: RegistrationSource::PUBLIC,
            submissionToken: 'unique_token_123',
            requestId: 'req-abc-001',
            ipAddress: '192.168.1.100',
        );

        $visit = $this->action->execute($dto);

        // 1. Visit assertion
        $this->assertInstanceOf(Visit::class, $visit);
        $this->assertSame('Rian Pratama', $visit->visitor_name);
        $this->assertSame('Dinas Kominfo', $visit->origin_organization);
        $this->assertSame($this->activeDept->id, $visit->department_id);
        $this->assertSame($this->activeDept->name, $visit->department_name_snapshot);
        $this->assertSame(VisitStatus::REGISTERED, $visit->current_status);
        $this->assertSame(RegistrationSource::PUBLIC, $visit->registration_source);
        $this->assertSame(2, $visit->party_size);
        $this->assertSame(26, strlen($visit->public_id));
        $this->assertSame(hash('sha256', 'unique_token_123'), $visit->submission_token_hash);

        // 2. Initial Visit Status History assertion
        $this->assertDatabaseHas('visit_status_history', [
            'visit_id' => $visit->id,
            'from_status' => null,
            'to_status' => 'REGISTERED',
            'source' => 'PUBLIC',
            'request_id' => 'req-abc-001',
        ]);

        // 3. Audit Log assertion
        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'visit.registered',
            'actor_type' => 'PUBLIC',
            'target_type' => 'visit',
            'target_id' => $visit->id,
            'target_public_id' => $visit->public_id,
            'ip_hash' => hash('sha256', '192.168.1.100'),
        ]);
    }

    public function test_register_visit_rejects_inactive_department(): void
    {
        $inactiveDept = Department::create([
            'public_id' => (string) Str::ulid(),
            'code' => 'ARSIP_LAMA',
            'name' => 'Bagian Arsip Lama',
            'is_active' => false,
        ]);

        $dto = new RegisterVisitDTO(
            visitorName: 'Joko',
            originOrganization: 'Umum',
            departmentId: $inactiveDept->id,
            purpose: 'Bertemu arsiparis',
        );

        $this->expectException(ModelNotFoundException::class);
        $this->action->execute($dto);

        $this->assertSame(0, Visit::count());
        $this->assertSame(0, VisitStatusHistory::count());
        $this->assertSame(0, AuditLog::count());
    }

    public function test_idempotency_returns_existing_visit_on_duplicate_token(): void
    {
        $token = 'idempotent_token_xyz';

        $dto1 = new RegisterVisitDTO(
            visitorName: 'Siti Aminah',
            originOrganization: 'Bappeda',
            departmentId: $this->activeDept->id,
            purpose: 'Koordinasi Rencana Kerja',
            submissionToken: $token,
        );

        $visit1 = $this->action->execute($dto1);

        // Attempt second registration with exact same submission token
        $dto2 = new RegisterVisitDTO(
            visitorName: 'Siti Aminah Resubmit',
            originOrganization: 'Bappeda',
            departmentId: $this->activeDept->id,
            purpose: 'Koordinasi Rencana Kerja',
            submissionToken: $token,
        );

        $visit2 = $this->action->execute($dto2);

        $this->assertSame($visit1->id, $visit2->id);
        $this->assertSame($visit1->public_id, $visit2->public_id);
        $this->assertSame(1, Visit::count());
        $this->assertSame(1, VisitStatusHistory::count());
    }

    public function test_transaction_rollback_when_audit_fails(): void
    {
        // Mock RecordAuditEventAction to throw exception
        $failingAuditMock = $this->createMock(RecordAuditEventAction::class);
        $failingAuditMock->method('execute')->willThrowException(new Exception('Audit failure simulation'));

        $this->app->instance(RecordAuditEventAction::class, $failingAuditMock);

        $action = $this->app->make(RegisterVisitAction::class);

        $dto = new RegisterVisitDTO(
            visitorName: 'Bambang',
            originOrganization: 'Dinas Kesehatan',
            departmentId: $this->activeDept->id,
            purpose: 'Simulasi Kegagalan Transaksi',
        );

        try {
            $action->execute($dto);
            $this->fail('Seharusnya melempar exception.');
        } catch (Exception $e) {
            $this->assertSame('Audit failure simulation', $e->getMessage());
        }

        // Verify rollback: no visit, no status history created!
        $this->assertSame(0, Visit::count());
        $this->assertSame(0, VisitStatusHistory::count());
        $this->assertSame(0, AuditLog::count());
    }
}
