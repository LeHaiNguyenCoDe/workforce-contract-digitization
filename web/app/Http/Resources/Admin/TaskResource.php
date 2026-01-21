<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'assignee_id' => $this->assignee_id,
            'assignee_name' => $this->assignee?->name,
            'assignee_avatar' => $this->assignee?->avatar,
            'created_by' => $this->created_by,
            'creator_name' => $this->creator?->name,
            'status' => $this->status,
            'priority' => $this->priority,
            'due_date' => $this->due_date?->toDateString(),
            'created_at' => $this->created_at?->toIso8601String(),
            'assignee' => $this->whenLoaded('assignee'),
            'creator' => $this->whenLoaded('creator'),
        ];
    }
}
