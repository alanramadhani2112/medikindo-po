<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierInvoiceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'goods_receipt_id'  => 'required|exists:goods_receipts,id',
            'invoice_number'    => 'required|string|unique:supplier_invoices',
            'due_date'          => 'required|date',
        ];
    }
}
