<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VenueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'max_capacity' => $this->max_capacity,
            'space_concept' => $this->space_concept,
            'type' => $this->type,
            'facilities' => $this->whenLoaded('facilities', fn() => $this->facilities->map(fn($f) => [
                'id' => $f->id,
                'name' => $f->name,
                'description' => $f->description,
            ])),
            'created_at' => $this->created_at,
        ];
    }
}
