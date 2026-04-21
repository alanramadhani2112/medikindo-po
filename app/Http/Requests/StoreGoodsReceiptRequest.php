<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGoodsReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Determine if this is a new GR (first delivery) or adding a delivery to existing GR
        $isNewGR = ! $this->input('goods_receipt_id');

        return [
            // --- GR-level fields (only required when creating a new GR) ---
            'purchase_order_id'          => $isNewGR ? 'required|exists:purchase_orders,id' : 'nullable',
            'goods_receipt_id'           => ! $isNewGR ? 'required|exists:goods_receipts,id' : 'nullable',

            // --- Delivery-level fields (required every time) ---
            'delivery_order_number'      => 'required|string|max:100',
            'delivery_photo'             => 'required|file|mimes:jpg,jpeg,png,webp|max:5120',
            'notes'                      => 'nullable|string|max:1000',

            // --- Items ---
            'items'                                  => 'required|array|min:1',
            'items.*.purchase_order_item_id'         => 'required|exists:purchase_order_items,id',
            'items.*.product_id'                     => 'required|exists:products,id',
            'items.*.quantity_received'              => 'required|integer|min:1',
            'items.*.batch_no'                       => 'required|string|max:100',
            'items.*.expiry_date'                    => 'required|date|after:today',
            'items.*.condition'                      => 'nullable|string',
            'items.*.notes'                          => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'purchase_order_id.required'             => 'Purchase Order wajib dipilih.',
            'delivery_order_number.required'         => 'Nomor surat jalan (DO) wajib diisi.',
            'delivery_order_number.max'              => 'Nomor surat jalan maksimal 100 karakter.',
            'delivery_photo.required'                => 'Foto bukti penerimaan barang wajib diupload.',
            'delivery_photo.mimes'                   => 'Foto harus berformat JPG, PNG, atau WebP.',
            'delivery_photo.max'                     => 'Ukuran foto maksimal 5MB.',
            'items.required'                         => 'Minimal harus ada 1 item yang diterima.',
            'items.*.quantity_received.required'     => 'Jumlah diterima wajib diisi.',
            'items.*.quantity_received.min'          => 'Jumlah diterima minimal 1.',
            'items.*.batch_no.required'              => 'Nomor batch wajib diisi.',
            'items.*.expiry_date.required'           => 'Tanggal kadaluarsa wajib diisi.',
            'items.*.expiry_date.after'              => 'Tidak dapat menerima barang kadaluarsa: tanggal expiry harus setelah hari ini.',
        ];
    }
}
