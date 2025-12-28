<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class OutboundReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'purpose' => ['required', 'in:sales,transfer,internal,return'],
            'destination_warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.stock_id' => ['required', 'exists:stocks,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
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
