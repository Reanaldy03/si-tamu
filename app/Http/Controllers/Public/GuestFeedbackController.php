<?php

namespace App\Http\Controllers\Public;

use App\Domain\Visits\Enums\VisitStatus;
use App\Http\Controllers\Controller;
use App\Models\OrganizationSetting;
use App\Models\Visit;
use App\Models\VisitFeedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestFeedbackController extends Controller
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
    public function store(Request $request, string $publicId): RedirectResponse
    {
        $visit = Visit::where('public_id', $publicId)->with('feedback')->firstOrFail();

        if ($visit->current_status !== VisitStatus::COMPLETED) {
            return redirect()
                ->route('public.feedback.create', ['publicId' => $publicId])
                ->with('error', 'Survei kepuasan hanya dapat diisi setelah kunjungan Anda selesai dilayani.');
        }

        if ($visit->feedback) {
            return redirect()
                ->route('public.feedback.create', ['publicId' => $publicId])
                ->with('info', 'Anda telah mengisi survei kepuasan untuk kunjungan ini.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'feedback_text' => ['nullable', 'string', 'max:1000'],
        ], [
            'rating.required' => 'Silakan pilih bintang penilaian Anda (1 s.d. 5).',
            'rating.between' => 'Nilai kepuasan harus berada pada rentang 1 hingga 5.',
            'feedback_text.max' => 'Saran dan tanggapan maksimal 1.000 karakter.',
        ]);

        VisitFeedback::create([
            'visit_id' => $visit->id,
            'rating' => (int) $validated['rating'],
            'feedback_text' => ! empty($validated['feedback_text']) ? strip_tags(trim($validated['feedback_text'])) : null,
            'submitted_at' => now(),
        ]);

        return redirect()
            ->route('public.feedback.create', ['publicId' => $publicId])
            ->with('success', 'Terima kasih atas partisipasi Anda! Penilaian Anda sangat berharga untuk meningkatkan kualitas pelayanan publik di Sekretariat DPRD Kota Binjai.');
    }
}
