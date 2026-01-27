<?php

namespace App\Http\Requests\Modules\Admin;

use Illuminate\Foundation\Http\FormRequest;

class InboundReceiptUpdateRequest extends FormRequest
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
            'warehouse_id' => 'sometimes|exists:warehouses,id',
            'supplier_id' => 'sometimes|exists:suppliers,id',
            'expected_date' => 'sometimes|nullable|date',
            'notes' => 'nullable|string',
            'items' => 'sometimes|array',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.unit_cost' => 'sometimes|numeric|min:0',
        ];
    }
}
