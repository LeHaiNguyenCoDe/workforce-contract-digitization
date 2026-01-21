<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'customer_id' => $this->customer_id,
            'customer_name' => $this->customer?->name,
            'user_id' => $this->user_id,
            'creator_name' => $this->creator?->name,
            'status' => $this->status,
            'valid_until' => $this->valid_until?->toDateString(),
            'total_amount' => (float) $this->total_amount,
            'note' => $this->note,
            'created_at' => $this->created_at?->toIso8601String(),
            'items' => $this->whenLoaded('items'),
            'customer' => $this->whenLoaded('customer'),
            'creator' => $this->whenLoaded('creator'),
        ];
    }
}
