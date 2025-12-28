<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class ReceiveBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'received_date' => ['nullable', 'date'],
            'items' => ['nullable', 'array'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.quantity_received' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
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
