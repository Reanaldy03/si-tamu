<?php

namespace App\Http\Controllers\Admin;

use App\Application\Visits\Actions\ChangeVisitStatusAction;
use App\Application\Visits\DTOs\ChangeVisitStatusDTO;
use App\Domain\Visits\Enums\VisitStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateVisitStatusRequest;
use App\Models\Visit;
use Illuminate\Http\RedirectResponse;

class VisitStatusController extends Controller
{
    /**
     * Update the operational status of a visit.
     */
    public function update(UpdateVisitStatusRequest $request, Visit $visit, ChangeVisitStatusAction $action): RedirectResponse
    {
        $this->authorize('updateStatus', $visit);

        $targetStatus = VisitStatus::from($request->validated('target_status'));
        $reason = $request->validated('reason') ?? 'Perubahan status operasional oleh petugas';

        $dto = new ChangeVisitStatusDTO(
            visitId: $visit->id,
            toStatus: $targetStatus,
            changedByUserId: $request->user()->id,
            source: $request->user()->primary_role_code?->value ?? 'STAFF',
            reason: $reason,
            requestId: $request->attributes->get('request_id'),
            ipAddress: $request->ip(),
        );

        $action->execute($dto);

        return redirect()
            ->route('admin.visits.show', $visit)
            ->with('success', "Status kunjungan berhasil diperbarui menjadi {$targetStatus->label()}.");
    }
}
