<?php

namespace App\Repositories;

use App\Models\Asset;

class AssetRepository extends BaseRepository
{
    public function __construct(Asset $asset)
    {
        parent::__construct($asset);
    }

    public function paginate(int $perPage = 15, array $filters = [])
    {
        $query = $this->query()->with([
            'purchaseOrder:id,po_number,vendor_id,procurement_type',
            'purchaseOrder.vendor:id,name,vendor_code',
            'purchaseOrderItem:id,purchase_order_id,item_name',
        ]);

        if (!empty($filters['purchase_order_id'])) {
            $query->where('purchase_order_id', $filters['purchase_order_id']);
        }

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%'.$filters['name'].'%');
        }

        return $query->latest()->paginate($perPage);
    }
}
