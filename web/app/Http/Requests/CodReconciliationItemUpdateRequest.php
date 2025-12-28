<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CodReconciliationItemUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:cod_reconciliation_items,id'],
            'items.*.received_amount' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator, response()->json([
            'status' => 'error',
            'message' => 'Validation error',
            'errors' => $validator->errors(),
        ], 422));
    }
}
