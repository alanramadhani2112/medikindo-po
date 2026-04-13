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
            'payment_date'        => 'nullable|date',
            'payment_method'      => 'required|string',
            'reference'           => 'nullable|string',
        ];
    }
}
