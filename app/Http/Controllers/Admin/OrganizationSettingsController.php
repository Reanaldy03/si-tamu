<?php

namespace App\Http\Controllers\Admin;

use App\Application\Audit\Actions\RecordAuditEventAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrganizationSettingsRequest;
use App\Models\OrganizationSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OrganizationSettingsController extends Controller
{
    /**
     * Show the form for editing the organization settings.
     */
    public function edit(): View
    {
        $this->authorize('manage', OrganizationSetting::class);

        $setting = OrganizationSetting::current();

        return view('admin.settings.edit', compact('setting'));
    }

    /**
     * Update the organization settings in storage.
     */
    public function update(
        UpdateOrganizationSettingsRequest $request,
        RecordAuditEventAction $recordAudit
    ): RedirectResponse {
        $setting = OrganizationSetting::current();
        $validated = $request->validated();

        $beforeState = [
            'organization_name' => $setting->organization_name,
            'address' => $setting->address,
            'contact_phone' => $setting->contact_phone,
            'contact_email' => $setting->contact_email,
            'registration_instruction' => $setting->registration_instruction,
            'privacy_notice' => $setting->privacy_notice,
            'timezone' => $setting->timezone,
            'logo_path' => $setting->logo_path,
        ];

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($setting->logo_path && Storage::disk('public')->exists($setting->logo_path)) {
                Storage::disk('public')->delete($setting->logo_path);
            }

            $path = $request->file('logo')->store('branding', 'public');
            $validated['logo_path'] = $path;
        }

        unset($validated['logo']);
        $validated['updated_by_user_id'] = $request->user()->id;

        $setting->update($validated);

        $afterState = [
            'organization_name' => $setting->organization_name,
            'address' => $setting->address,
            'contact_phone' => $setting->contact_phone,
            'contact_email' => $setting->contact_email,
            'registration_instruction' => $setting->registration_instruction,
            'privacy_notice' => $setting->privacy_notice,
            'timezone' => $setting->timezone,
            'logo_path' => $setting->logo_path,
        ];

        $recordAudit->execute(
            eventType: 'settings.updated',
            actorType: 'USER',
            actorUserId: $request->user()->id,
            targetType: 'organization_settings',
            targetId: $setting->id,
            targetPublicId: (string) $setting->id,
            requestId: $request->attributes->get('request_id'),
            ipAddress: $request->ip(),
            beforeState: $beforeState,
            afterState: $afterState
        );

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', 'Pengaturan organisasi dan identitas sistem berhasil disimpan.');
    }
}
