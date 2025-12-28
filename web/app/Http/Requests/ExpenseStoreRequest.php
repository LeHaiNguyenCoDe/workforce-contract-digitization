<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:expense_categories,id'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'fund_id' => ['nullable', 'exists:funds,id'],
            'type' => ['required', 'in:expense,income'],
            'amount' => ['required', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_recurring' => ['nullable', 'boolean'],
            'recurring_period' => ['nullable', 'in:daily,weekly,monthly,yearly'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Danh mục là bắt buộc.',
            'amount.required' => 'Số tiền là bắt buộc.',
            'expense_date.required' => 'Ngày giao dịch là bắt buộc.',
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
