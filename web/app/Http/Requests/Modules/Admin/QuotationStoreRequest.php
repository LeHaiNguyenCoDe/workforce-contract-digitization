<?php

namespace App\Http\Requests\Modules\Admin;

use Illuminate\Foundation\Http\FormRequest;

class QuotationStoreRequest extends FormRequest
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
            'code' => 'required|string|unique:quotations,code', // Phải có mã và không được trùng
            'customer_id' => 'required|exists:users,id',      // Phải có khách hàng tồn tại trong bảng users
            'valid_until' => 'required|date|after_or_equal:today', // Phải là ngày tháng và không được là quá khứ
            'items' => 'required|array|min:1',                // Bào giá phải có ít nhất 1 sản phẩm
            'items.*.product_id' => 'required|exists:products,id', // Từng sản phẩm phải tồn tại
            'items.*.quantity' => 'required|integer|min:1',   // Số lượng phải >= 1
        ];
    }
}
