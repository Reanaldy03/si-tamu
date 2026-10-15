<?php

namespace App\Http\Requests\Admin;

use App\Domain\Access\Enums\RoleCode;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('manage', User::class);
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? strip_tags(trim($this->name)) : null,
            'email' => is_string($this->email) ? strtolower(trim($this->email)) : null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $targetUser = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($targetUser?->id)],
            'password' => ['nullable', 'string', 'min:8', 'max:100'],
            'role' => ['required', new Enum(RoleCode::class)],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * Configure validator for self-protection rules.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $targetUser = $this->route('user');

            if ($targetUser && $this->user()->id === $targetUser->id) {
                // Rule 1: Admin cannot deactivate themselves
                if (! $this->boolean('is_active')) {
                    $validator->errors()->add('is_active', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
                }

                // Rule 2: Admin cannot remove own Admin role
                if ($this->input('role') !== RoleCode::ADMIN->value) {
                    $validator->errors()->add('role', 'Anda tidak dapat menghapus hak akses Administrator dari akun Anda sendiri.');
                }
            }
        });
    }

    /**
     * Custom messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'password.min' => 'Password minimal 8 karakter jika ingin diubah.',
            'role.required' => 'Peran (role) wajib dipilih.',
        ];
    }
}
