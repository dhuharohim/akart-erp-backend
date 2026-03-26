<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
            'event_number' => $this->event_number,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'status' => $this->status,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'budget_amount' => $this->budget_amount,
            'revenue_amount' => $this->revenue_amount,
            'expense_amount' => $this->expense_amount,
            'profit_amount' => $this->profit_amount,
            'created_at' => $this->created_at,
        ];
    }
}
