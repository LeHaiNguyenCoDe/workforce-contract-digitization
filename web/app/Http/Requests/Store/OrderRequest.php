<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
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
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email'],
            'address_line' => ['required', 'string', 'max:255'],
            'ward' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'string', 'in:cod,credit_card,bank_transfer,e_wallet'],
            'note' => ['nullable', 'string'],
            'promotion_code' => ['nullable', 'string', 'max:50'],
            'use_points' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'full_name.required' => 'The full name field is required.',
            'phone.required' => 'The phone field is required.',
            'email.email' => 'The email must be a valid email address.',
            'address_line.required' => 'The address line field is required.',
            'payment_method.required' => 'The payment method field is required.',
            'payment_method.in' => 'The payment method must be one of: cod, credit_card, bank_transfer, e_wallet.',
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

