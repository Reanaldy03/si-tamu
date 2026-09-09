<?php

namespace App\Http\Controllers\Public;

use App\Application\Visits\Actions\RegisterVisitAction;
use App\Application\Visits\DTOs\RegisterVisitDTO;
use App\Application\Visits\Services\VisitQrTokenService;
use App\Domain\Visits\Enums\RegistrationSource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreGuestVisitRequest;
use App\Models\Department;
use App\Models\OrganizationSetting;
use App\Models\Visit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GuestRegistrationController extends Controller
{
    /**
     * Show the public guest registration form.
     */
    public function create(): View
    {
        $departments = Department::active()->get();
        $organization = OrganizationSetting::current();
        $submissionToken = (string) Str::uuid();

        return view('public.registration.create', compact('departments', 'organization', 'submissionToken'));
    }

    /**
     * Store a newly created guest visit in storage.
     */
    public function store(StoreGuestVisitRequest $request, RegisterVisitAction $registerVisitAction): RedirectResponse
    {
        $validated = $request->validated();

        $dto = new RegisterVisitDTO(
            visitorName: $validated['visitor_name'],
            originOrganization: $validated['origin_organization'],
            departmentId: (int) $validated['department_id'],
            purpose: $validated['purpose'],
            contactPhone: $validated['contact_phone'] ?? null,
            visitorEmail: $validated['visitor_email'] ?? null,
            visitorAddress: $validated['visitor_address'] ?? null,
            visitorOccupation: $validated['visitor_occupation'] ?? null,
            visitorGender: $validated['visitor_gender'] ?? null,
            visitorIdentityType: $validated['visitor_identity_type'] ?? null,
            visitorIdentityNumber: $validated['visitor_identity_number'] ?? null,
            partySize: (int) ($validated['party_size'] ?? 1),
            notes: $validated['notes'] ?? null,
            registrationSource: RegistrationSource::PUBLIC,
            createdByUserId: null,
            submissionToken: $validated['submission_token'] ?? null,
            requestId: $request->attributes->get('request_id'),
            ipAddress: $request->ip(),
        );

        $visit = $registerVisitAction->execute($dto);

        return redirect()
            ->route('public.registration.success', ['publicId' => $visit->public_id])
            ->with('registered_public_id', $visit->public_id);
    }

    /**
     * Display the registration confirmation and receipt.
     */
    public function success(string $publicId, VisitQrTokenService $qrTokenService): View
    {
        $visit = Visit::where('public_id', $publicId)->firstOrFail();
        $organization = OrganizationSetting::current();

        $qrPayload = $qrTokenService->generateQrPayload($visit);
        $qrSvg = $qrTokenService->renderSvg($qrPayload, 200);

        return view('public.registration.success', compact('visit', 'organization', 'qrSvg'));
    }

    /**
     * Display printable visitor pass / ticket with official DPRD Kota Binjai kop.
     */
    public function printTicket(string $publicId, VisitQrTokenService $qrTokenService): View
    {
        $visit = Visit::where('public_id', $publicId)->firstOrFail();
        $organization = OrganizationSetting::current();

        $qrPayload = $qrTokenService->generateQrPayload($visit);
        $qrSvg = $qrTokenService->renderSvg($qrPayload, 220);

        return view('public.registration.ticket-print', compact('visit', 'organization', 'qrSvg'));
    }
}
