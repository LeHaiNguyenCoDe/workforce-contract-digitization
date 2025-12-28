<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BatchStoreRequest extends FormRequest
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
            'batch_code' => ['nullable', 'string', 'max:50', 'unique:batches,batch_code'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'manufacturing_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after:manufacturing_date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Sản phẩm là bắt buộc.',
            'quantity.required' => 'Số lượng là bắt buộc.',
            'unit_cost.required' => 'Giá nhập là bắt buộc.',
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
