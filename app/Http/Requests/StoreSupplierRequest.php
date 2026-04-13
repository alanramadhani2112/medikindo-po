<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $supplierId = $this->route('supplier')?->id;

        return [
            'name'           => ['required', 'string', 'max:255'],
            'code'           => [
                'required',
                'string',
                'max:20',
                'unique:suppliers,code' . ($supplierId ? ",{$supplierId}" : ''),
            ],
            'address'        => ['nullable', 'string'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'email'          => ['nullable', 'email'],
            'npwp'           => ['nullable', 'string', 'max:30'],
            'license_number' => ['nullable', 'string', 'max:100'],
        ];
    }
}
