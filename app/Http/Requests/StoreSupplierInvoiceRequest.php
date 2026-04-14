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
            'goods_receipt_id'              => 'required|exists:goods_receipts,id',
            'distributor_invoice_number'    => 'required|string|max:255',
            'distributor_invoice_date'      => 'required|date',
            'due_date'                      => 'required|date|after_or_equal:distributor_invoice_date',
            'internal_invoice_number'       => 'nullable|string|max:255|unique:supplier_invoices,invoice_number',
            'notes'                         => 'nullable|string',
            'items'                         => 'required|array|min:1',
            'items.*.goods_receipt_item_id' => 'required|exists:goods_receipt_items,id',
            'items.*.quantity'              => 'required|numeric|min:0.01',
            'items.*.unit_price'            => 'required|numeric|min:0',
            'items.*.discount_percent'      => 'nullable|numeric|min:0|max:100',
        ];
    }

    public function messages()
    {
        return [
            'distributor_invoice_number.required' => 'Nomor invoice distributor wajib diisi.',
            'distributor_invoice_date.required'   => 'Tanggal invoice distributor wajib diisi.',
            'due_date.after_or_equal'             => 'Tanggal jatuh tempo harus sama atau setelah tanggal invoice.',
            'items.required'                      => 'Minimal harus ada 1 item.',
            'items.*.quantity.required'           => 'Quantity wajib diisi untuk setiap item.',
            'items.*.unit_price.required'         => 'Harga satuan wajib diisi untuk setiap item.',
        ];
    }
}
