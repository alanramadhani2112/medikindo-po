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
            'selling_price'       => ['required', 'numeric', 'min:0', 'gt:cost_price'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount'     => ['nullable', 'numeric', 'min:0', 'lte:selling_price'],
            
            'is_narcotic'         => ['boolean'],
            'description'         => ['nullable', 'string'],
            
            // Expiry tracking fields
            'expiry_date'         => ['nullable', 'date', 'after:today'],
            'batch_no'            => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'selling_price.gt' => 'Harga jual harus lebih besar dari harga beli untuk mendapatkan profit.',
            'discount_amount.lte' => 'Diskon tidak boleh lebih besar dari harga jual.',
            'expiry_date.after' => 'Tanggal kadaluarsa harus setelah hari ini.',
        ];
    }
}
