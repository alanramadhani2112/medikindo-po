<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id'              => ['sometimes', 'integer', 'exists:suppliers,id'],
            'requested_date'           => ['nullable', 'date'],
            'expected_delivery_date'   => ['nullable', 'date', 'after_or_equal:requested_date'],
            'notes'                    => ['nullable', 'string', 'max:2000'],
        ];
    }
}
