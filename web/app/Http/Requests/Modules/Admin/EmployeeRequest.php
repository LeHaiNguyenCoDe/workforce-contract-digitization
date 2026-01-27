<?php

namespace App\Http\Requests\Modules\Admin;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');

        return [
            'name' => $isUpdate ? 'sometimes|string|max:255' : 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'join_date' => 'nullable|date',
            'base_salary' => 'nullable|numeric|min:0',
            'user_id' => $isUpdate ? 'nullable|exists:users,id' : 'nullable|exists:users,id',
            'status' => 'sometimes|in:active,inactive,on_leave',
        ];
    }
}
