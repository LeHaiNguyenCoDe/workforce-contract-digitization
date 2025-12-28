<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class MembershipTierUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:100'],
            'min_points' => ['nullable', 'integer', 'min:0'],
            'max_points' => ['nullable', 'integer', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'point_multiplier' => ['nullable', 'numeric', 'min:1'],
            'benefits' => ['nullable', 'array'],
            'color' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
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
