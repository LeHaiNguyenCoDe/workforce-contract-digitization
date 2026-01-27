<?php

namespace App\Http\Requests\Modules\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', 'unique:products,slug'],
            'price' => ['sometimes', 'integer', 'min:0'],
            'sale_price' => ['nullable', 'integer', 'min:0'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'specs' => ['sometimes', 'array'],
            'images' => ['sometimes', 'array'],
            'tags' => ['sometimes', 'array'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'manufacturer_name' => ['nullable', 'string', 'max:255'],
            'manufacturer_brand' => ['nullable', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'faqs' => ['sometimes', 'array'],
            'variants' => ['sometimes', 'array'],
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
