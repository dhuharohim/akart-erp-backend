<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'purchase_order_id' => $this->purchase_order_id,
            'purchase_order_item_id' => $this->purchase_order_item_id,
            'name' => $this->name,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'acquisition_cost' => $this->acquisition_cost,
            'depreciation_rate' => $this->depreciation_rate,
            'purchase_order' => $this->purchaseOrder ? [
                'id' => $this->purchaseOrder->id,
                'po_number' => $this->purchaseOrder->po_number,
                'vendor_id' => $this->purchaseOrder->vendor_id,
                'procurement_type' => $this->purchaseOrder->procurement_type,
                'vendor' => $this->purchaseOrder->vendor ? [
                    'id' => $this->purchaseOrder->vendor->id,
                    'name' => $this->purchaseOrder->vendor->name,
                    'vendor_code' => $this->purchaseOrder->vendor->vendor_code,
                ] : null,
            ] : null,
            'created_at' => $this->created_at,
        ];
    }
}
