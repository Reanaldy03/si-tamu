<?php

namespace App\Http\Requests\Admin;

use App\Domain\Visits\Enums\VisitStatus;
use App\Models\ReportExport;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('export', ReportExport::class);
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('q') && is_string($this->q)) {
            $this->merge(['q' => strip_tags(trim($this->q))]);
        }
        if ($this->has('format') && is_string($this->format)) {
            $this->merge(['format' => strtoupper(trim($this->format))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'format' => ['required', 'string', 'in:CSV,XLSX'],
            'q' => ['nullable', 'string', 'max:100'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'current_status' => ['nullable', Rule::enum(VisitStatus::class)],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ];
    }
}
