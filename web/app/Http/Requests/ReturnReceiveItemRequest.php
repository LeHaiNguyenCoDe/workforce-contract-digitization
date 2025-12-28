<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReturnReceiveItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:return_items,id'],
            'items.*.received_quantity' => ['required', 'integer', 'min:0'],
            'items.*.condition' => ['required', 'in:new,good,damaged,defective'],
            'items.*.action' => ['nullable', 'in:restock,dispose,repair'],
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
