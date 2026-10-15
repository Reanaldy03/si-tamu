<?php

namespace App\Http\Requests\Admin;

use App\Models\OrganizationSetting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrganizationSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('manage', OrganizationSetting::class);
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'organization_name' => is_string($this->organization_name) ? strip_tags(trim($this->organization_name)) : null,
            'address' => is_string($this->address) ? strip_tags(trim($this->address)) : null,
            'contact_phone' => is_string($this->contact_phone) ? trim($this->contact_phone) : null,
            'contact_email' => is_string($this->contact_email) ? trim($this->contact_email) : null,
            'registration_instruction' => is_string($this->registration_instruction) ? strip_tags(trim($this->registration_instruction)) : null,
            'privacy_notice' => is_string($this->privacy_notice) ? strip_tags(trim($this->privacy_notice)) : null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'organization_name' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'contact_email' => ['nullable', 'email', 'max:100'],
            'registration_instruction' => ['required', 'string', 'max:500'],
            'privacy_notice' => ['required', 'string', 'max:500'],
            'timezone' => ['required', 'string', 'in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura,UTC'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ];
    }

    /**
     * Custom messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'organization_name.required' => 'Nama organisasi / instansi wajib diisi.',
            'registration_instruction.required' => 'Instruksi registrasi tamu wajib diisi.',
            'privacy_notice.required' => 'Pemberitahuan privasi wajib diisi.',
            'timezone.required' => 'Zona waktu wajib dipilih.',
            'logo.image' => 'File logo harus berupa gambar valid.',
            'logo.max' => 'Ukuran file logo maksimal 2MB.',
        ];
    }
}
