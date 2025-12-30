<?php

namespace App\Http\Requests\Modules\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $action = $this->route()->getActionMethod();

        return match($action) {
            'addItem' => [
                'product_id' => ['required', 'integer', 'exists:products,id'],
                'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
                'qty' => ['required', 'integer', 'min:1'],
            ],
            'updateItem' => [
                'qty' => ['required', 'integer', 'min:1'],
            ],
            default => [],
        };
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'product_id.required' => 'The product ID field is required.',
            'product_id.exists' => 'The selected product does not exist.',
            'variant_id.exists' => 'The selected variant does not exist.',
            'qty.required' => 'The quantity field is required.',
            'qty.min' => 'The quantity must be at least 1.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
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

