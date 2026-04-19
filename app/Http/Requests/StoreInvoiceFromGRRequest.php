<?php

namespace App\Http\Requests;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreInvoiceFromGRRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create_invoices');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'goods_receipt_id' => [
                'required',
                'exists:goods_receipts,id',
            ],
            'custom_invoice_number' => 'nullable|string|max:255|unique:customer_invoices,invoice_number',
            'due_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string|max:1000',
            'surcharge' => 'nullable|numeric|min:0',
            
            'items' => 'required|array|min:1',
            'items.*.goods_receipt_item_id' => [
                'required',
                'exists:goods_receipt_items,id',
            ],
            'items.*.quantity' => [
                'required',
                'numeric',
                'min:1',
            ],
            // NOTE: unit_price, discount, tax will be taken from PO item (not from user input)
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validateGoodsReceipt($validator);
            $this->validateItems($validator);
        });
    }

    /**
     * Validate Goods Receipt
     */
    protected function validateGoodsReceipt(Validator $validator): void
    {
        $grId = $this->input('goods_receipt_id');
        
        if (!$grId) {
            return;
        }

        $gr = GoodsReceipt::with('purchaseOrder.supplier')->find($grId);

        if (!$gr) {
            return;
        }

        // Rule: GR must be completed
        if ($gr->status !== GoodsReceipt::STATUS_COMPLETED) {
            $validator->errors()->add(
                'goods_receipt_id',
                "Goods Receipt must be 'completed'. Current status: {$gr->status}."
            );
        }

        // Rule: GR must have a valid PO with supplier
        if (!$gr->purchaseOrder || !$gr->purchaseOrder->supplier_id) {
            $validator->errors()->add(
                'goods_receipt_id',
                'Goods Receipt must have a valid Purchase Order with supplier.'
            );
        }

        // Rule: GR must have remaining quantity (AR)
        if (!$gr->hasRemainingArQuantity()) {
            $validator->errors()->add(
                'goods_receipt_id',
                'Goods Receipt is fully invoiced (Customer Billing). No remaining quantity available.'
            );
        }
    }

    /**
     * Validate Items
     */
    protected function validateItems(Validator $validator): void
    {
        $grId = $this->input('goods_receipt_id');
        $items = $this->input('items', []);

        if (!$grId || empty($items)) {
            return;
        }

        foreach ($items as $index => $item) {
            $grItemId = $item['goods_receipt_item_id'] ?? null;
            $quantity = $item['quantity'] ?? 0;

            if (!$grItemId) {
                continue;
            }

            $grItem = GoodsReceiptItem::with('purchaseOrderItem.product')->find($grItemId);

            if (!$grItem) {
                continue;
            }

            // Rule: GR item must belong to selected GR
            if ($grItem->goods_receipt_id != $grId) {
                $validator->errors()->add(
                    "items.{$index}.goods_receipt_item_id",
                    'This item does not belong to the selected Goods Receipt.'
                );
                continue;
            }

            // Rule: Quantity must not exceed remaining quantity (AR)
            $remainingQty = $grItem->remaining_ar_quantity;
            
            if ($quantity > $remainingQty) {
                $productName = $grItem->purchaseOrderItem->product->name ?? 'Unknown';
                $validator->errors()->add(
                    "items.{$index}.quantity",
                    "Quantity ({$quantity}) exceeds remaining quantity ({$remainingQty}) for {$productName}."
                );
            }
        }
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'goods_receipt_id.required' => 'Goods Receipt harus dipilih.',
            'goods_receipt_id.exists' => 'Goods Receipt tidak ditemukan.',
            'custom_invoice_number.unique' => 'Nomor invoice sudah digunakan.',
            'due_date.required' => 'Tanggal jatuh tempo harus diisi.',
            'due_date.after_or_equal' => 'Tanggal jatuh tempo harus hari ini atau setelahnya.',
            'items.required' => 'Minimal 1 item harus diisi.',
            'items.*.goods_receipt_item_id.required' => 'Item Goods Receipt harus dipilih.',
            'items.*.goods_receipt_item_id.exists' => 'Item Goods Receipt tidak ditemukan.',
            'items.*.quantity.required' => 'Jumlah harus diisi.',
            'items.*.quantity.min' => 'Jumlah minimal 1.',
        ];
    }
}
