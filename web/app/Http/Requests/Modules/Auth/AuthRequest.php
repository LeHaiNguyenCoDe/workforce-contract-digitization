<?php

namespace App\Http\Requests\Modules\Auth;

use Illuminate\Foundation\Http\FormRequest;

class AuthRequest extends FormRequest
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
        // Determine which action is being performed based on route
        $route = $this->route();
        $action = $route ? $route->getActionMethod() : null;

        return match($action) {
            'login' => $this->loginRules(),
            'register' => $this->registerRules(),
            'logout' => $this->logoutRules(),
            'me' => $this->meRules(),
            default => [],
        };
    }

    /**
     * Validation rules for login action
     *
     * @return array
     */
    protected function loginRules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
            'remember' => 'sometimes|boolean',
        ];
    }

    /**
     * Validation rules for register action
     *
     * @return array
     */
    protected function registerRules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ];
    }

    /**
     * Validation rules for logout action
     *
     * @return array
     */
    protected function logoutRules()
    {
        return [
            // Logout doesn't require any input validation
        ];
    }

    /**
     * Validation rules for me action
     *
     * @return array
     */
    protected function meRules()
    {
        return [
            // Me endpoint doesn't require any input validation
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
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'password.required' => 'The password field is required.',
            'remember.boolean' => 'The remember field must be true or false.',
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

