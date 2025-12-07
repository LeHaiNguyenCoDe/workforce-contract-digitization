<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class UserRequest extends FormRequest
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
            //
        ];
    }

    public function messages()
    {
        return array(
            'name.required' => 'The name field is required.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email has already been taken.',
            'email.max' => 'The email may not be greater than 255 characters.',
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.max' => 'The password may not be greater than 255 characters.',
            'password.regex' => 'The password must contain at least one letter, one number, and one special character (@, $, !, %, *, ?, or &).',
        );
    }

    /**
     * Get the validation rules that apply to the request for store.
     *
     * @return mixed
     */
    public function storeValidator()
    {
        $params = array(
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
        );

        $rules = array(
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|min:8|max:255|regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
        );

        $messages = $this->messages();
        $validator = Validator::make($params, $rules, $messages);

        return $validator->errors();
    }

    /**
     * Get the validation rules that apply to the request for update.
     *
     * @return mixed
     */
    public function updateValidator()
    {
        $user = $this->route('user');

        $params = array(
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
        );

        $rules = array(
            'name' => 'required|max:255',
            'email' => "required|max:255|email|unique:users,email,{$user->id}",
        );

        if ($this->password != '') {
            $rules['password'] = 'required|min:8|max:255|regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/';
        }

        $messages = $this->messages();
        $validator = Validator::make($params, $rules, $messages);

        return $validator->errors();
    }
}

