<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_warehouse_id' => ['required', 'exists:warehouses,id'],
            'to_warehouse_id' => ['required', 'exists:warehouses,id', 'different:from_warehouse_id'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.batch_id' => ['nullable', 'exists:batches,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'from_warehouse_id.required' => 'Kho xuất là bắt buộc.',
            'to_warehouse_id.required' => 'Kho nhập là bắt buộc.',
            'to_warehouse_id.different' => 'Kho nhập phải khác kho xuất.',
            'items.required' => 'Phải có ít nhất 1 sản phẩm.',
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
