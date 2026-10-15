<?php

namespace App\Http\Controllers\Admin;

use App\Application\Visits\Actions\CheckInVisitViaQrAction;
use App\Http\Controllers\Controller;
use App\Models\OrganizationSetting;
use App\Models\Visit;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VisitCheckInController extends Controller
{
    /**
     * Show the camera QR scanner interface for receptionists.
     */
    public function showScanner(): View
    {
        $this->authorize('checkIn', Visit::class);

        return view('admin.visits.scanner');
    }

    /**
     * Process check-in via scanned QR payload or manual ULID entry.
     */
    public function processCheckIn(Request $request, CheckInVisitViaQrAction $checkInAction): JsonResponse
    {
        $this->authorize('checkIn', Visit::class);

        $validated = $request->validate([
            'payload' => ['required', 'string', 'max:500'],
        ]);

        try {
            $visit = $checkInAction->execute(
                rawInput: $validated['payload'],
                actorUserId: (int) Auth::id(),
                requestId: $request->attributes->get('request_id'),
                ipAddress: $request->ip(),
            );

            $organization = OrganizationSetting::current();
            $tz = $organization?->timezone ?? config('app.timezone', 'Asia/Jakarta');
            $tzAbbr = match ($tz) {
                'Asia/Makassar' => 'WITA',
                'Asia/Jayapura' => 'WIT',
                default => 'WIB',
            };
            $formattedTime = ($visit->checked_in_at ?? now())->timezone($tz)->format('H:i').' '.$tzAbbr;

            return response()->json([
                'success' => true,
                'message' => 'Check-in berhasil diverifikasi.',
                'data' => [
                    'public_id' => $visit->public_id,
                    'visitor_name' => $visit->visitor_name,
                    'origin_organization' => $visit->origin_organization,
                    'department' => $visit->department_name_snapshot,
                    'purpose' => $visit->purpose,
                    'party_size' => $visit->party_size,
                    'current_status' => $visit->current_status->label(),
                    'checked_in_at' => $formattedTime,
                    'detail_url' => route('admin.visits.show', $visit),
                ],
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat memproses check-in.',
            ], 500);
        }
    }
}
