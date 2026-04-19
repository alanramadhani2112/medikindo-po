<?php

namespace App\Http\Requests;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validateGoodsReceipt($validator);
            $this->validateItems($validator);
        });
    }

    protected function validateGoodsReceipt(Validator $validator): void
    {
        $grId = $this->input('goods_receipt_id');
        if (!$grId) return;

        $gr = GoodsReceipt::find($grId);
        if (!$gr) return;

        if (!$gr->hasRemainingQuantity()) { // AP check
            $validator->errors()->add('goods_receipt_id', 'Goods Receipt is fully invoiced (Supplier Payables).');
        }
    }

    protected function validateItems(Validator $validator): void
    {
        $grId = $this->input('goods_receipt_id');
        $items = $this->input('items', []);
        if (!$grId || empty($items)) return;

        foreach ($items as $index => $item) {
            $grItemId = $item['goods_receipt_item_id'] ?? null;
            $quantity = $item['quantity'] ?? 0;
            if (!$grItemId) continue;

            $grItem = GoodsReceiptItem::find($grItemId);
            if (!$grItem) continue;

            $remaining = $grItem->remaining_ap_quantity;
            if ($quantity > $remaining) {
                $validator->errors()->add("items.{$index}.quantity", "Quantity ({$quantity}) exceeds remaining AP quantity ({$remaining}).");
            }
        }
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
