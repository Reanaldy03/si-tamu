<?php

declare(strict_types=1);

namespace Tests\Feature\Feedback;

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

class VisitFeedbackTest extends TestCase
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
        $response->assertSee('Survei Kepuasan Layanan (IKM)');
        $response->assertSee($visit->visitor_name);

        $postResponse = $this->post(route('public.feedback.store', ['publicId' => $visit->public_id]), [
            'rating' => 5,
            'feedback_text' => 'Pelayanan sangat memuaskan, petugas ramah dan cepat.',
        ], ['REMOTE_ADDR' => '192.168.1.100']);

        $postResponse->assertRedirect(route('public.feedback.create', ['publicId' => $visit->public_id]));
        $postResponse->assertSessionHas('success');

        $this->assertDatabaseHas('visit_feedback', [
            'visit_id' => $visit->id,
            'rating' => 5,
            'feedback_text' => 'Pelayanan sangat memuaskan, petugas ramah dan cepat.',
            'ip_address' => '192.168.1.100',
        ]);
    }

    public function test_guest_cannot_submit_feedback_if_visit_is_not_completed(): void
    {
        $nonCompletedStatuses = [
            VisitStatus::REGISTERED,
            VisitStatus::ARRIVED,
            VisitStatus::IN_SERVICE,
        ];

        foreach ($nonCompletedStatuses as $status) {
            $visit = $this->createVisitWithStatus($status);

            $getResponse = $this->get(route('public.feedback.create', ['publicId' => $visit->public_id]));
            $getResponse->assertOk();
            $getResponse->assertSee('Kunjungan Masih Berlangsung');

            $postResponse = $this->post(route('public.feedback.store', ['publicId' => $visit->public_id]), [
                'rating' => 4,
                'feedback_text' => 'Pelayanan belum selesai tapi mencoba isi.',
            ]);

            $postResponse->assertSessionHas('error', 'Survei hanya dapat diisi setelah kunjungan selesai.');
            $this->assertDatabaseMissing('visit_feedback', [
                'visit_id' => $visit->id,
            ]);
        }
    }

    public function test_guest_cannot_submit_feedback_twice(): void
    {
        $visit = $this->createVisitWithStatus(VisitStatus::COMPLETED);

        // Submit first feedback
        $firstResponse = $this->post(route('public.feedback.store', ['publicId' => $visit->public_id]), [
            'rating' => 5,
            'feedback_text' => 'Kunjungan pertama kali, sangat puas.',
        ]);
        $firstResponse->assertSessionHas('success');
        $this->assertDatabaseCount('visit_feedback', 1);

        // Attempt second submission
        $secondResponse = $this->post(route('public.feedback.store', ['publicId' => $visit->public_id]), [
            'rating' => 1,
            'feedback_text' => 'Ingin mengganti ulasan jadi buruk.',
        ]);

        $secondResponse->assertSessionHas('info', 'Anda telah mengisi survei kepuasan untuk kunjungan ini.');

        // Assert rating remains 5 and count is still 1
        $this->assertDatabaseCount('visit_feedback', 1);
        $this->assertDatabaseHas('visit_feedback', [
            'visit_id' => $visit->id,
            'rating' => 5,
            'feedback_text' => 'Kunjungan pertama kali, sangat puas.',
        ]);
    }

    public function test_dashboard_ikm_calculates_average_and_distribution_correctly(): void
    {
        // Create 4 visits with ratings: 5, 4, 3, 5
        // Sum = 17, Count = 4, Average = 4.25 -> 4.3 (1 decimal place)
        // Rating 5: 2 (50%)
        // Rating 4: 1 (25%)
        // Rating 3: 1 (25%)
        // Rating 2: 0 (0%)
        // Rating 1: 0 (0%)
        $ratings = [5, 4, 3, 5];
        foreach ($ratings as $index => $rating) {
            $visit = $this->createVisitWithStatus(VisitStatus::COMPLETED, "Tamu Uji {$index}");
            VisitFeedback::create([
                'visit_id' => $visit->id,
                'rating' => $rating,
                'feedback_text' => "Ulasan untuk rating {$rating} nomor {$index}",
                'ip_address' => '127.0.0.1',
                'submitted_at' => now()->subMinutes(10 - $index),
            ]);
        }

        $response = $this->actingAs($this->admin)->get(route('admin.feedback.index'));

        $response->assertOk();
        $response->assertSee('Indeks Kepuasan Layanan (IKM)');
        $response->assertSee('4.3'); // Average formatted to 1 decimal
        $response->assertSee('4'); // Total respondents
        $response->assertSee('50%'); // Percentage for 5 stars
        $response->assertSee('25%'); // Percentage for 4 and 3 stars

        // Test rating query filter
        $filterResponse = $this->actingAs($this->admin)->get(route('admin.feedback.index', ['rating' => 3]));
        $filterResponse->assertOk();
        $filterResponse->assertSee('Ulasan untuk rating 3');
        $filterResponse->assertDontSee('Ulasan untuk rating 4');
    }

    protected function createVisitWithStatus(VisitStatus $status, string $visitorName = 'Budi Santoso'): Visit
    {
        $dto = new RegisterVisitDTO(
            visitorName: $visitorName,
            originOrganization: 'Dinas Kominfo',
            departmentId: $this->department->id,
            purpose: 'Konsultasi Layanan Publik',
            contactPhone: '081298765432',
            partySize: 1,
            registrationSource: RegistrationSource::PUBLIC,
        );

        $visit = app(RegisterVisitAction::class)->execute($dto);
        $visit->current_status = $status;
        $visit->save();

        return $visit;
    }
}
