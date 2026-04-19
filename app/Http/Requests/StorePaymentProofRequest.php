<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled in controller/policy
    }

    public function rules(): array
    {
        return [
            'customer_invoice_id' => 'required|exists:customer_invoices,id',
            'amount'              => 'required|numeric|min:0.01',
            'payment_date'        => 'required|date|before_or_equal:today',
            'bank_reference'      => 'nullable|string|max:100',
            'notes'               => 'nullable|string|max:500',
            'file'                => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB
        ];
    }
}
