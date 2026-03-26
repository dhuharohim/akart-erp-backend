<?php

namespace App\Services;

use App\Models\Invoice;
use App\Repositories\InvoiceRepository;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(private InvoiceRepository $invoices) {}

    public function paginate(int $perPage = 15, array $filters = [])
    {
        return $this->invoices->paginate($perPage, $filters);
    }

    public function create(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $details = $data['details'] ?? $data['items'] ?? [];
            unset($data['details'], $data['items']);

            $invoice = $this->invoices->create($data);
            $totalAmount = 0;
            foreach ($details as $detail) {
                $subtotal = (float) $detail['quantity'] * (float) $detail['unit_price'];
                $invoice->details()->create([
                    'description' => $detail['description'],
                    'unit' => $detail['unit'] ?? null,
                    'quantity' => $detail['quantity'],
                    'unit_price' => $detail['unit_price'],
                    'subtotal' => $subtotal,
                ]);
                $totalAmount += $subtotal;
            }
            if (!isset($data['total_amount']) && $totalAmount > 0) {
                $invoice->update(['total_amount' => $totalAmount]);
            }

            return $invoice->fresh();
        });
    }

    public function update(Invoice $invoice, array $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data) {
            $details = $data['details'] ?? $data['items'] ?? null;
            unset($data['details'], $data['items']);
            $invoice = $this->invoices->update($invoice, $data);

            if ($details !== null) {
                $invoice->details()->delete();
                $totalAmount = 0;
                foreach ($details as $detail) {
                    $subtotal = (float) $detail['quantity'] * (float) $detail['unit_price'];
                    $invoice->details()->create([
                        'description' => $detail['description'],
                        'unit' => $detail['unit'] ?? null,
                        'quantity' => $detail['quantity'],
                        'unit_price' => $detail['unit_price'],
                        'subtotal' => $subtotal,
                    ]);
                    $totalAmount += $subtotal;
                }
                $invoice->update(['total_amount' => $totalAmount]);
            }

            return $invoice->fresh();
        });
    }

    public function delete(Invoice $invoice): bool
    {
        return DB::transaction(fn() => $this->invoices->delete($invoice));
    }
}
