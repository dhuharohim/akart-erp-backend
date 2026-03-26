<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
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
            'po_number' => $this->po_number,
            'po_date' => $this->po_date,
            'event_id' => $this->event_id,
            'event_series_id' => $this->event_series_id,
            'vendor_id' => $this->vendor_id,
            'procurement_type' => $this->procurement_type,
            'status' => $this->status,
            'total_amount' => $this->total_amount,
            'approved_at' => $this->approved_at,
            'items' => PurchaseOrderItemResource::collection($this->whenLoaded('items')),
            'vendor' => new VendorResource($this->whenLoaded('vendor')),
            'series' => new EventSeriesResource($this->whenLoaded('series')),
            'created_at' => $this->created_at,
        ];
    }
}
