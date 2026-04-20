<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGoodsReceiptRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'purchase_order_id'          => 'required|exists:purchase_orders,id',
            'delivery_order_number'      => 'required|string|max:255',
            'items'                      => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.product_id'          => 'required|exists:products,id',
            'items.*.quantity_received'  => 'required|integer|min:1',
            'items.*.batch_no'           => 'required|string|max:255',
            'items.*.expiry_date'        => 'required|date',
            'items.*.condition'          => 'nullable|string',
            'items.*.notes'              => 'nullable|string',
            'notes'                      => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'delivery_order_number.required' => 'Nomor surat jalan (DO) wajib diisi.',
            'delivery_order_number.string'   => 'Nomor surat jalan harus berupa teks.',
            'delivery_order_number.max'      => 'Nomor surat jalan maksimal 255 karakter.',
            'purchase_order_id.required'     => 'Purchase Order wajib dipilih.',
            'items.required'                 => 'Minimal harus ada 1 item yang diterima.',
            'items.*.quantity_received.required' => 'Jumlah diterima wajib diisi.',
            'items.*.batch_no.required'      => 'Nomor batch wajib diisi.',
            'items.*.expiry_date.required'   => 'Tanggal kadaluarsa wajib diisi.',
        ];
    }
}
