<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'level'    => ['required', 'integer', Rule::in([1, 2])],
            'decision' => ['required', 'string', Rule::in(['approved', 'rejected'])],
            'notes'    => ['nullable', 'string', 'max:2000'],
        ];
    }
}
