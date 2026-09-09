<?php

namespace App\Http\Requests\Public;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGuestVisitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'visitor_name' => is_string($this->visitor_name) ? strip_tags(trim($this->visitor_name)) : $this->visitor_name,
            'origin_organization' => is_string($this->origin_organization) ? strip_tags(trim($this->origin_organization)) : $this->origin_organization,
            'purpose' => is_string($this->purpose) ? strip_tags(trim($this->purpose)) : $this->purpose,
            'contact_phone' => is_string($this->contact_phone) && trim($this->contact_phone) !== '' ? trim($this->contact_phone) : null,
            'visitor_email' => is_string($this->visitor_email) && trim($this->visitor_email) !== '' ? trim(strtolower($this->visitor_email)) : null,
            'party_size' => $this->filled('party_size') ? (int) $this->party_size : 1,
            'notes' => is_string($this->notes) && trim($this->notes) !== '' ? strip_tags(trim($this->notes)) : null,
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
            'visitor_name' => ['required', 'string', 'max:100'],
            'origin_organization' => ['required', 'string', 'max:150'],
            'department_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id')->where('is_active', true),
            ],
            'purpose' => ['required', 'string', 'max:500'],
            'contact_phone' => ['nullable', 'string', 'max:20', 'regex:/^([0-9\s\-\+\(\)]*)$/'],
            'visitor_email' => ['nullable', 'email', 'max:100'],
            'visitor_address' => ['nullable', 'string', 'max:255'],
            'visitor_occupation' => ['nullable', 'string', 'max:100'],
            'visitor_gender' => ['nullable', 'string', 'in:MALE,FEMALE,OTHER'],
            'visitor_identity_type' => ['nullable', 'string', 'max:20'],
            'visitor_identity_number' => ['nullable', 'string', 'max:50'],
            'party_size' => ['nullable', 'integer', 'min:1', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
            'submission_token' => ['nullable', 'string', 'max:64'],
            'website_hp' => ['nullable', 'string', 'max:0'], // Honeypot field must be empty
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('website_hp')) {
                $validator->errors()->add('website_hp', 'Aktivitas tidak diizinkan.');
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'visitor_name.required' => 'Nama lengkap wajib diisi.',
            'visitor_name.max' => 'Nama lengkap maksimal 100 karakter.',
            'origin_organization.required' => 'Asal instansi / organisasi wajib diisi.',
            'origin_organization.max' => 'Asal instansi maksimal 150 karakter.',
            'department_id.required' => 'Unit / bidang tujuan wajib dipilih.',
            'department_id.exists' => 'Unit / bidang tujuan yang dipilih tidak valid atau sedang tidak aktif.',
            'purpose.required' => 'Keperluan kunjungan wajib diisi.',
            'purpose.max' => 'Keperluan kunjungan maksimal 500 karakter.',
            'contact_phone.regex' => 'Format nomor kontak tidak valid.',
            'contact_phone.max' => 'Nomor kontak maksimal 20 karakter.',
            'visitor_email.email' => 'Format email tidak valid.',
            'party_size.min' => 'Jumlah rombongan minimal 1 orang.',
            'party_size.max' => 'Jumlah rombongan maksimal 100 orang.',
        ];
    }
}
