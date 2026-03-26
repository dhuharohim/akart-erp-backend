<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalLineResource extends JsonResource
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
            'journal_id' => $this->journal_id,
            'account_id' => $this->account_id,
            'description' => $this->description,
            'debit' => $this->debit,
            'credit' => $this->credit,
            'account' => new ChartOfAccountResource($this->whenLoaded('account')),
        ];
    }
}
