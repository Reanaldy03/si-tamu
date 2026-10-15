<?php

namespace Tests\Feature\Public;

use App\Application\Visits\Actions\RegisterVisitAction;
use App\Application\Visits\DTOs\RegisterVisitDTO;
use App\Domain\Access\Enums\RoleCode;
use App\Domain\Visits\Enums\RegistrationSource;
use App\Domain\Visits\Enums\VisitStatus;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitFeedback;
use Database\Seeders\DepartmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestFeedbackTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->seed(DepartmentSeeder::class);

        $this->department = Department::first();

        $adminRole = Role::where('slug', RoleCode::ADMIN->value)->first();
        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->roles()->attach($adminRole->id, ['assigned_at' => now()]);
    }

    public function test_guest_can_submit_feedback_for_completed_visit(): void
    {
        $visit = $this->createVisitWithStatus(VisitStatus::COMPLETED);

        $response = $this->get(route('public.feedback.create', ['publicId' => $visit->public_id]));
        $response->assertOk();
        $response->assertSee('Tingkat Kepuasan Pelayanan');

        $postResponse = $this->post(route('public.feedback.store', ['publicId' => $visit->public_id]), [
            'rating' => 5,
            'feedback_text' => 'Pelayanan sangat memuaskan, petugas ramah dan informatif.',
        ]);

        $postResponse->assertRedirect(route('public.feedback.create', ['publicId' => $visit->public_id]));
        $postResponse->assertSessionHas('success');

        $this->assertDatabaseHas('visit_feedback', [
            'visit_id' => $visit->id,
            'rating' => 5,
            'feedback_text' => 'Pelayanan sangat memuaskan, petugas ramah dan informatif.',
        ]);
    }

    public function test_guest_cannot_submit_feedback_if_visit_not_completed(): void
    {
        $visit = $this->createVisitWithStatus(VisitStatus::ARRIVED);

        $response = $this->get(route('public.feedback.create', ['publicId' => $visit->public_id]));
        $response->assertOk();
        $response->assertSee('Kunjungan Masih Berlangsung');

        $postResponse = $this->post(route('public.feedback.store', ['publicId' => $visit->public_id]), [
            'rating' => 4,
            'feedback_text' => 'Cukup baik.',
        ]);

        $postResponse->assertSessionHas('error');
        $this->assertDatabaseCount('visit_feedback', 0);
    }

    public function test_guest_cannot_submit_duplicate_feedback(): void
    {
        $visit = $this->createVisitWithStatus(VisitStatus::COMPLETED);

        // First feedback
        VisitFeedback::create([
            'visit_id' => $visit->id,
            'rating' => 5,
            'feedback_text' => 'Bagus sekali',
            'submitted_at' => now(),
        ]);

        // Attempt second feedback
        $response = $this->post(route('public.feedback.store', ['publicId' => $visit->public_id]), [
            'rating' => 1,
            'feedback_text' => 'Ubah pendapat',
        ]);

        $response->assertSessionHas('info');

        // Rating in database should still be 5
        $this->assertDatabaseHas('visit_feedback', [
            'visit_id' => $visit->id,
            'rating' => 5,
        ]);
        $this->assertDatabaseCount('visit_feedback', 1);
    }

    public function test_feedback_rating_validation_rules(): void
    {
        $visit = $this->createVisitWithStatus(VisitStatus::COMPLETED);

        // Missing rating
        $response1 = $this->post(route('public.feedback.store', ['publicId' => $visit->public_id]), [
            'rating' => '',
        ]);
        $response1->assertSessionHasErrors(['rating']);

        // Out of bound rating (e.g. 6 or 0)
        $response2 = $this->post(route('public.feedback.store', ['publicId' => $visit->public_id]), [
            'rating' => 6,
        ]);
        $response2->assertSessionHasErrors(['rating']);
    }

    public function test_admin_can_view_feedback_index_and_see_ikm_aggregates(): void
    {
        $visit1 = $this->createVisitWithStatus(VisitStatus::COMPLETED);
        $visit2 = $this->createVisitWithStatus(VisitStatus::COMPLETED);

        VisitFeedback::create(['visit_id' => $visit1->id, 'rating' => 5, 'feedback_text' => 'Mantap', 'submitted_at' => now()]);
        VisitFeedback::create(['visit_id' => $visit2->id, 'rating' => 4, 'feedback_text' => 'Bagus', 'submitted_at' => now()]);

        $response = $this->actingAs($this->admin)->get(route('admin.feedback.index'));
        $response->assertOk();
        $response->assertSee('Indeks Kepuasan Layanan (IKM)');
        $response->assertSee('4.5'); // (5+4)/2
        $response->assertSee('Mantap');
        $response->assertSee('Bagus');
    }

    protected function createVisitWithStatus(VisitStatus $status): Visit
    {
        $dto = new RegisterVisitDTO(
            visitorName: 'Dewi Lestari',
            originOrganization: 'Akademisi USU',
            departmentId: $this->department->id,
            purpose: 'Penelitian perundang-undangan',
            contactPhone: '081233334444',
            partySize: 1,
            registrationSource: RegistrationSource::PUBLIC,
        );

        $visit = app(RegisterVisitAction::class)->execute($dto);
        $visit->current_status = $status;
        $visit->save();

        return $visit;
    }
}
