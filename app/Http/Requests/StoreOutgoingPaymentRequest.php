<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOutgoingPaymentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'supplier_invoice_id' => 'required|exists:supplier_invoices,id',
            'amount'              => 'required|numeric|min:0.01',
            'payment_date'        => 'nullable|date|before_or_equal:today',
            'payment_method'      => 'required|string|in:Bank Transfer,Cash,Virtual Account,Giro,Cek,QRIS',
            'bank_account_id'     => 'nullable|exists:bank_accounts,id',
            'bank_name_manual'    => 'nullable|string|max:100',
            'reference'           => 'nullable|string|max:100',
            'description'         => 'nullable|string|max:500',
            'surcharge_amount'    => 'nullable|numeric|min:0',
            'surcharge_percentage'=> 'nullable|numeric|min:0|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_invoice_id.required' => 'Invoice supplier wajib dipilih.',
            'amount.required'              => 'Nominal pembayaran wajib diisi.',
            'amount.min'                   => 'Nominal pembayaran minimal Rp 1.',
            'payment_method.required'      => 'Metode pembayaran wajib dipilih.',
            'payment_method.in'            => 'Metode pembayaran tidak valid.',
            'payment_date.before_or_equal' => 'Tanggal pembayaran tidak boleh di masa depan.',
        ];
    }
}
