<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryAlertSettingRequest extends FormRequest
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
            'min_quantity' => ['required', 'integer', 'min:0'],
            'max_quantity' => ['required', 'integer', 'min:0'],
            'reorder_quantity' => ['nullable', 'integer', 'min:0'],
            'auto_create_purchase_request' => ['boolean'],
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
