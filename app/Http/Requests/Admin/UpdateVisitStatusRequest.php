<?php

namespace App\Http\Requests\Admin;

use App\Domain\Visits\Enums\VisitStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateVisitStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $visit = $this->route('visit');

        return $this->user() !== null && $this->user()->can('updateStatus', $visit);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'target_status' => ['required', new Enum(VisitStatus::class)],
            'reason' => [
                Rule::requiredIf(fn () => $this->input('target_status') === VisitStatus::CANCELLED->value),
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'target_status.required' => 'Status tujuan wajib dipilih.',
            'reason.required' => 'Alasan pembatalan kunjungan wajib diisi.',
            'reason.max' => 'Alasan pembatalan maksimal 255 karakter.',
        ];
    }
}
