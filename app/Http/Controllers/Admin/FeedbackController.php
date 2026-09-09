<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VisitFeedback;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    /**
     * Display list of guest satisfaction feedback reviews and IKM metrics.
     */
    public function index(Request $request): View
    {
        $query = VisitFeedback::with(['visit.department'])->latest('submitted_at');

        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->input('rating'));
        }

        $feedbacks = $query->paginate(15)->withQueryString();

        // Calculate aggregated IKM statistics
        $totalCount = VisitFeedback::count();
        $avgRating = $totalCount > 0 ? (float) round(VisitFeedback::avg('rating'), 2) : 0.0;

        $rawDist = VisitFeedback::selectRaw('rating, count(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->all();

        $distribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = $rawDist[$i] ?? 0;
            $distribution[$i] = [
                'count' => $count,
                'percentage' => $totalCount > 0 ? round(($count / $totalCount) * 100, 1) : 0,
            ];
        }

        return view('admin.feedback.index', compact('feedbacks', 'totalCount', 'avgRating', 'distribution'));
    }
}
