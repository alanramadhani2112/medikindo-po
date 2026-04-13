<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by Policy
    }

    public function rules(): array
    {
        return [
            'supplier_id'              => ['required', 'integer', 'exists:suppliers,id'],
            'requested_date'           => ['nullable', 'date', 'after_or_equal:today'],
            'expected_delivery_date'   => ['nullable', 'date', 'after_or_equal:requested_date'],
            'notes'                    => ['nullable', 'string', 'max:2000'],
        ];
    }
}
