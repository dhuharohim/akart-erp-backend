<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalResource extends JsonResource
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
            'journal_number' => $this->journal_number,
            'date' => $this->date?->format('Y-m-d'),
            'description' => $this->description,
            'reference' => $this->reference,
            'status' => $this->status,
            'total_debit' => $this->total_debit,
            'total_credit' => $this->total_credit,
            'lines' => JournalLineResource::collection($this->whenLoaded('lines')),
            'created_at' => $this->created_at,
        ];
    }
}
