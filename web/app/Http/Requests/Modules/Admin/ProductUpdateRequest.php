<?php

namespace App\Http\Requests\Modules\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id ?? $this->route('product');
        return [
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', 'unique:products,slug,' . $productId],
            'price' => ['sometimes', 'integer', 'min:0'],
            'sale_price' => ['sometimes', 'integer', 'min:0'],
            'short_description' => ['sometimes', 'string'],
            'description' => ['sometimes', 'string'],
            'thumbnail' => ['sometimes', 'string', 'url'],
            'is_active' => ['sometimes', 'boolean'],
            'specs' => ['sometimes', 'array'],
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
