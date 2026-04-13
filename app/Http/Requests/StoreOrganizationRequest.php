<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $organizationId = $this->route('organization')?->id;

        return [
            'name'           => ['required', 'string', 'max:255'],
            'type'           => ['required', 'string', 'in:clinic,hospital'], // Added for type differentiation
            'code'           => [
                'required',
                'string',
                'max:20',
                'unique:organizations,code' . ($organizationId ? ",{$organizationId}" : ''),
            ],
            'address'        => ['nullable', 'string'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'email'          => ['nullable', 'email'],
            'license_number' => ['nullable', 'string', 'max:100'],
        ];
    }
}
