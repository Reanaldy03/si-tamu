<?php

declare(strict_types=1);

namespace App\Http\Requests\Public;

use App\Models\Visit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreVisitFeedbackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'feedback_text' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom attribute names and validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rating.required' => 'Silakan pilih bintang penilaian Anda (1 s.d. 5).',
            'rating.integer' => 'Penilaian harus berupa angka bulat.',
            'rating.between' => 'Nilai kepuasan harus berada pada rentang 1 hingga 5.',
            'feedback_text.max' => 'Saran dan tanggapan maksimal 1.000 karakter.',
        ];
    }

    /**
     * Helper to retrieve the visit instance associated with this public feedback route.
     */
    public function getVisit(): Visit
    {
        return Visit::where('public_id', $this->route('publicId'))->firstOrFail();
    }
}
