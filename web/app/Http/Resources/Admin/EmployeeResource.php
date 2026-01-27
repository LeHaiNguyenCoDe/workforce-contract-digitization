<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_code' => $this->employee_code,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'department' => $this->department,
            'position' => $this->position,
            'join_date' => $this->join_date?->toDateString(),
            'base_salary' => (float) $this->base_salary,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'user' => $this->whenLoaded('user'),
            'attendances' => $this->whenLoaded('attendances'),
        ];
    }
}
