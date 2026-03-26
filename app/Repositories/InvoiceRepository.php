<?php

namespace App\Repositories;

use App\Models\Invoice;

class InvoiceRepository extends BaseRepository
{
    public function __construct(Invoice $invoice)
    {
        parent::__construct($invoice);
    }

    public function paginate(int $perPage = 15, array $filters = [])
    {
        $query = $this->query()->with('details');

        if (!empty($filters['related_type'])) {
            $query->where('related_type', $filters['related_type']);
        }
        if (!empty($filters['related_id'])) {
            $query->where('related_id', $filters['related_id']);
        }
        if (!empty($filters['invoice_number'])) {
            $query->where('invoice_number', 'like', "%{$filters['invoice_number']}%");
        }

        return $query->latest()->paginate($perPage);
    }
}
