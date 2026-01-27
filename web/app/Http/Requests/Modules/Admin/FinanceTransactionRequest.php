<?php

namespace App\Http\Requests\Modules\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FinanceTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:receipt,payment'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'fund_id' => ['sometimes', 'exists:funds,id'],
            'category_id' => ['nullable', 'exists:expense_categories,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'transaction_date' => ['sometimes', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Loại giao dịch là bắt buộc.',
            'amount.required' => 'Số tiền là bắt buộc.',
            'amount.min' => 'Số tiền phải lớn hơn 0.',
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
