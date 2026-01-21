<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'order_number' => $this->order_number,
            'user_id' => $this->user_id,
            'user_name' => $this->user?->name,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address_line' => $this->address_line,
            'ward' => $this->ward,
            'district' => $this->district,
            'province' => $this->province,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'total_amount' => (float) $this->total_amount,
            'paid_amount' => (float) $this->paid_amount,
            'remaining_amount' => (float) $this->remaining_amount,
            'cost_amount' => (float) $this->cost_amount,
            'note' => $this->note,
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'items' => $this->whenLoaded('items'),
            'user' => $this->whenLoaded('user'),
            'shipments' => $this->whenLoaded('shipments'),
            'stock_check' => $this->when(isset($this->stock_check), $this->stock_check),
        ];
    }
}
