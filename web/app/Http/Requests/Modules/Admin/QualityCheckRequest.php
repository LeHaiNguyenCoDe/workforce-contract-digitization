<?php

namespace App\Http\Requests\Modules\Admin;

use Illuminate\Foundation\Http\FormRequest;

class QualityCheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inbound_batch_id' => ['required', 'exists:inbound_batches,id'],
            'product_id' => ['required', 'exists:products,id'],
            'check_date' => ['nullable', 'date'],
            'status' => ['required', 'in:pass,fail,partial'],
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'quantity_passed' => ['required', 'integer', 'min:0'],
            'quantity_failed' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
            'issues' => ['nullable', 'array'],
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
