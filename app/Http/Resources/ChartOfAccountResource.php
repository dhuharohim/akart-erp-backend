<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChartOfAccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'parent_id' => $this->parent_id,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'children' => ChartOfAccountResource::collection($this->whenLoaded('children')),
            'created_at' => $this->created_at,
        ];
    }
}
