<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncPOItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.product_id'      => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'        => ['required', 'integer', 'min:1'],
            'items.*.unit_price'      => ['nullable', 'numeric', 'min:0'],
            'items.*.notes'           => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'At least one item is required.',
            'items.min'      => 'At least one item is required.',
        ];
    }
}
