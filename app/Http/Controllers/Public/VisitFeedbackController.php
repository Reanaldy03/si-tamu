<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Domain\Visits\Enums\VisitStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreVisitFeedbackRequest;
use App\Models\OrganizationSetting;
use App\Models\Visit;
use App\Models\VisitFeedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VisitFeedbackController extends Controller
{
    /**
     * Show the public feedback survey form for a completed visit.
     */
    public function create(string $publicId): View
    {
        $visit = Visit::where('public_id', $publicId)->with('feedback')->firstOrFail();
        $organization = OrganizationSetting::current();

        return view('public.feedback.create', compact('visit', 'organization'));
    }

    /**
     * Store the guest satisfaction feedback.
     */
    public function store(StoreVisitFeedbackRequest $request, string $publicId): RedirectResponse
    {
        $visit = $request->getVisit();

        if ($visit->current_status !== VisitStatus::COMPLETED) {
            return redirect()
                ->route('public.feedback.create', ['publicId' => $publicId])
                ->with('error', 'Survei hanya dapat diisi setelah kunjungan selesai.');
        }

        if ($visit->feedback()->exists()) {
            return redirect()
                ->route('public.feedback.create', ['publicId' => $publicId])
                ->with('info', 'Anda telah mengisi survei kepuasan untuk kunjungan ini.');
        }

        $validated = $request->validated();

        VisitFeedback::create([
            'visit_id' => $visit->id,
            'rating' => (int) $validated['rating'],
            'feedback_text' => ! empty($validated['feedback_text']) ? strip_tags(trim((string) $validated['feedback_text'])) : null,
            'ip_address' => $request->ip(),
            'submitted_at' => now(),
        ]);

        return redirect()
            ->route('public.feedback.create', ['publicId' => $publicId])
            ->with('success', 'Terima kasih atas partisipasi Anda dalam memajukan pelayanan Sekretariat DPRD Kota Binjai.');
    }

    /**
     * Display the feedback survey success acknowledgment page.
     */
    public function success(string $publicId): View
    {
        $visit = Visit::where('public_id', $publicId)->with('feedback')->firstOrFail();
        $organization = OrganizationSetting::current();

        return view('public.feedback.success', compact('visit', 'organization'));
    }
}
