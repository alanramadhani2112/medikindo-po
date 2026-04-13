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
            'items'                      => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity_received'  => 'required|integer|min:1',
            'items.*.condition'          => 'nullable|string',
            'items.*.notes'              => 'nullable|string',
            'notes'                      => 'nullable|string',
        ];
    }
}
