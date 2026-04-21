<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_bank_accounts');
    }

    public function rules(): array
    {
        return [
            'bank_name'           => 'required|string|max:100',
            'bank_code'           => 'nullable|string|max:10',
            'account_number'      => 'required|string|max:30|unique:bank_accounts,account_number',
            'account_holder_name' => 'required|string|max:100',
            'account_type'        => 'required|in:receive,send,both',
            'notes'               => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'bank_name.required'           => 'Nama bank wajib diisi.',
            'bank_name.max'                => 'Nama bank maksimal 100 karakter.',
            'account_number.required'      => 'Nomor rekening wajib diisi.',
            'account_number.max'           => 'Nomor rekening maksimal 30 karakter.',
            'account_number.unique'        => 'Nomor rekening sudah terdaftar.',
            'account_holder_name.required' => 'Nama pemilik rekening wajib diisi.',
            'account_holder_name.max'      => 'Nama pemilik rekening maksimal 100 karakter.',
        ];
    }
}
