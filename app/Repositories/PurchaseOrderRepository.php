<?php

namespace App\Repositories;

use App\Models\PurchaseOrder;

class PurchaseOrderRepository extends BaseRepository
{
    public function __construct(PurchaseOrder $purchaseOrder)
    {
        parent::__construct($purchaseOrder);
    }

    public function paginate(int $perPage = 15, array $filters = [])
    {
        $query = $this->query()->with(['vendor', 'items', 'series']);

        if (!empty($filters['po_number'])) {
            $query->where('po_number', 'like', '%' . $filters['po_number'] . '%');
        }

        if (!empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        if (!empty($filters['event_id'])) {
            $query->where('event_id', $filters['event_id']);
        }

        if (!empty($filters['event_series_id'])) {
            $query->where('event_series_id', $filters['event_series_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['procurement_type'])) {
            $query->where('procurement_type', $filters['procurement_type']);
        }

        if (!empty($filters['po_date'])) {
            $query->whereDate('po_date', $filters['po_date']);
        }

        return $query->latest()->paginate($perPage);
    }
}
