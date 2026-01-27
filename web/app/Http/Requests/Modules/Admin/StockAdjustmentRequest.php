<?php

namespace App\Http\Requests\Modules\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['sometimes', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:0'],
            'available_quantity' => ['sometimes', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'min:3'],
            'note' => ['nullable', 'string'],
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
