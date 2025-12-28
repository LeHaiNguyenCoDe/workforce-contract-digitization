<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequestStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'requested_quantity' => ['required', 'integer', 'min:1'],
            'current_stock' => ['nullable', 'integer', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
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
