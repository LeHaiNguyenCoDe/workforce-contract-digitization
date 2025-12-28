<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BatchUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'manufacturing_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'status' => ['nullable', 'in:available,reserved,expired,depleted'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $response = response()->json([
            'status' => 'error',
            'message' => 'Validation error',
            'errors' => $validator->errors(),
        ], 422);

        throw new \Illuminate\Validation\ValidationException($validator, $response);
    }
}
