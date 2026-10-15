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
            'letter_number' => is_string($this->letter_number) && trim($this->letter_number) !== '' ? strip_tags(trim($this->letter_number)) : null,
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
            'visit_date' => ['required', 'date_format:Y-m-d'],
            'letter_number' => ['nullable', 'string', 'max:100'],
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
            'visitor_name.string' => 'Nama lengkap harus berupa teks.',
            'visitor_name.max' => 'Nama lengkap maksimal 100 karakter.',
            'origin_organization.required' => 'Asal instansi / organisasi wajib diisi.',
            'origin_organization.string' => 'Asal instansi / organisasi harus berupa teks.',
            'origin_organization.max' => 'Asal instansi maksimal 150 karakter.',
            'department_id.required' => 'Unit / bidang tujuan wajib dipilih.',
            'department_id.integer' => 'Unit / bidang tujuan tidak valid.',
            'department_id.exists' => 'Unit / bidang tujuan yang dipilih tidak valid atau sedang tidak aktif.',
            'purpose.required' => 'Keperluan kunjungan wajib diisi.',
            'purpose.string' => 'Keperluan kunjungan harus berupa teks.',
            'purpose.max' => 'Keperluan kunjungan maksimal 500 karakter.',
            'visit_date.required' => 'Tanggal kunjungan wajib diisi.',
            'visit_date.date_format' => 'Format tanggal kunjungan tidak valid.',
            'letter_number.string' => 'Nomor surat harus berupa teks.',
            'letter_number.max' => 'Nomor surat maksimal 100 karakter.',
            'contact_phone.string' => 'Nomor kontak harus berupa teks.',
            'contact_phone.regex' => 'Format nomor kontak tidak valid.',
            'contact_phone.max' => 'Nomor kontak maksimal 20 karakter.',
            'visitor_email.email' => 'Format email tidak valid.',
            'visitor_email.max' => 'Email maksimal 100 karakter.',
            'visitor_address.string' => 'Alamat harus berupa teks.',
            'visitor_address.max' => 'Alamat maksimal 255 karakter.',
            'visitor_occupation.string' => 'Pekerjaan harus berupa teks.',
            'visitor_occupation.max' => 'Pekerjaan maksimal 100 karakter.',
            'visitor_gender.string' => 'Jenis kelamin tidak valid.',
            'visitor_gender.in' => 'Jenis kelamin tidak valid.',
            'visitor_identity_type.string' => 'Jenis identitas tidak valid.',
            'visitor_identity_type.max' => 'Jenis identitas maksimal 20 karakter.',
            'visitor_identity_number.string' => 'Nomor identitas harus berupa teks.',
            'visitor_identity_number.max' => 'Nomor identitas maksimal 50 karakter.',
            'party_size.integer' => 'Jumlah rombongan harus berupa angka.',
            'party_size.min' => 'Jumlah rombongan minimal 1 orang.',
            'party_size.max' => 'Jumlah rombongan maksimal 100 orang.',
            'notes.string' => 'Catatan tambahan harus berupa teks.',
            'notes.max' => 'Catatan tambahan maksimal 500 karakter.',
            'submission_token.string' => 'Token pengiriman tidak valid.',
            'submission_token.max' => 'Token pengiriman maksimal 64 karakter.',
            'website_hp.string' => 'Aktivitas tidak diizinkan.',
            'website_hp.max' => 'Aktivitas tidak diizinkan.',
        ];
    }
}
