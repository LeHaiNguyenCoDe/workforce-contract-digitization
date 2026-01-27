<?php

namespace App\Http\Requests\Modules\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StockAdjustmentStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'warehouse_id' => 'required|exists:warehouses,id',
            'stock_id' => 'required|exists:stocks,id',
            'previous_quantity' => 'required|integer|min:0',
            'new_quantity' => 'required|integer|min:0',
            'adjustment_quantity' => 'required|integer',
            'reason' => 'required|string|min:3', // BR-05.2: Bắt buộc có lý do
            'notes' => 'nullable|string',
        ];
    }
}
