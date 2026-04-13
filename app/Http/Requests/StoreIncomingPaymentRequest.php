<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncomingPaymentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'customer_invoice_id' => 'required|exists:customer_invoices,id',
            'amount'              => 'required|numeric|min:0.01',
            'payment_date'        => 'nullable|date',
            'payment_method'      => 'required|string',
            'reference'           => 'nullable|string',
        ];
    }
}
