<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Handled in UserController via Gate
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;
        $isSuperAdmin = $this->user()->isSuperAdmin();

        $rules = [
            'name'      => ['sometimes', 'string', 'max:255'],
            'email'     => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'is_active' => ['sometimes', 'boolean'],
        ];

        // Only Super Admin can change organization assignment and role
        if ($isSuperAdmin) {
            $rules['organization_id'] = ['nullable', 'integer', 'exists:organizations,id'];
            $rules['role']            = ['sometimes', 'string', 'exists:roles,name'];
        }

        return $rules;
    }
}
