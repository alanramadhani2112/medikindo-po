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
            'name'                   => ['required', 'string', 'max:255'],
            'code'                   => [
                'required',
                'string',
                'max:20',
                'unique:suppliers,code' . ($supplierId ? ",{$supplierId}" : ''),
            ],
            'address'                => ['nullable', 'string'],
            'phone'                  => ['nullable', 'string', 'max:20'],
            'email'                  => ['nullable', 'email'],
            'npwp'                   => ['nullable', 'string', 'max:30'],
            'license_number'         => ['required', 'string', 'max:100', 'unique:suppliers,license_number' . ($supplierId ? ",{$supplierId}" : '')],
            'license_expiry_date'    => ['nullable', 'date', 'after:today'],
            'is_authorized_narcotic' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                   => 'Nama supplier wajib diisi.',
            'code.required'                   => 'Kode supplier wajib diisi.',
            'code.unique'                     => 'Kode supplier sudah digunakan.',
            'license_number.required'         => 'Nomor izin wajib diisi.',
            'license_number.unique'           => 'Nomor izin sudah digunakan.',
            'license_expiry_date.after'       => 'Tanggal kadaluarsa izin harus setelah hari ini.',
            'email.email'                     => 'Format email tidak valid.',
        ];
    }
}
