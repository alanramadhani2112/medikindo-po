<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'supplier_id'         => ['required', 'integer', 'exists:suppliers,id'],
            'name'                => ['required', 'string', 'max:255'],
            'sku'                 => [
                'required',
                'string',
                'max:50',
                'unique:products,sku' . ($productId ? ",{$productId}" : ''),
            ],
            'category'            => ['nullable', 'string', 'max:100'],
            'unit'                => ['required', 'string', 'max:30'],
            'price'               => ['nullable', 'numeric', 'min:0'],
            
            // Profit calculation fields
            'cost_price'          => ['required', 'numeric', 'min:0'],
            'selling_price'       => ['required', 'numeric', 'min:0'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount'     => ['nullable', 'numeric', 'min:0'],
            
            'is_narcotic'         => ['boolean'],
            'description'         => ['nullable', 'string'],
        ];
    }
}
