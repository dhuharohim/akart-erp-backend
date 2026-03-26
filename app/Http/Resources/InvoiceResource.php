<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'invoice_number' => $this->invoice_number,
            'related_type' => $this->related_type,
            'related_id' => $this->related_id,
            'issue_date' => $this->issue_date,
            'due_date' => $this->due_date,
            'status' => $this->status,
            'total_amount' => $this->total_amount,
            'details' => $this->details->map(fn($detail) => [
                'id' => $detail->id,
                'description' => $detail->description,
                'unit' => $detail->unit,
                'quantity' => $detail->quantity,
                'unit_price' => $detail->unit_price,
                'subtotal' => $detail->subtotal,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
