<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\ChartOfAccount;
use App\Models\EventSeries;
use App\Models\Invoice;
use App\Models\Journal;
use App\Models\PurchaseOrder;
use App\Models\VendorItem;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use App\Repositories\PurchaseOrderRepository;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    public function __construct(
        private PurchaseOrderRepository $purchaseOrders,
        private DocumentNumberService $numberService,
    ) {}

    public function paginate(int $perPage = 15, array $filters = [])
    {
        return $this->purchaseOrders->paginate($perPage, $filters);
    }

    public function create(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $data['po_number'] = $this->numberService->generate(PurchaseOrder::class);
            $data = $this->normalizeSeriesRelation($data);
            $data['total_amount'] = $this->calculateTotal($items);

            $po = $this->purchaseOrders->create($data);

            $this->syncLineItems($po, $items);
            $this->syncAssets($po);

            return $po->load(['items', 'vendor', 'assets', 'series']);
        });
    }

    public function update(PurchaseOrder $purchaseOrder, array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $data) {
            $items = $data['items'] ?? null;
            unset($data['items']);
            $data = $this->normalizeSeriesRelation($data);

            if ($items !== null) {
                $data['total_amount'] = $this->calculateTotal($items);
            }

            $purchaseOrder = $this->purchaseOrders->update($purchaseOrder, $data);

            if ($items !== null) {
                $purchaseOrder->items()->forceDelete();
                $this->syncLineItems($purchaseOrder, $items);
            }

            $this->syncAssets($purchaseOrder);

            return $purchaseOrder->load(['items', 'vendor', 'assets', 'series']);
        });
    }

    public function delete(PurchaseOrder $purchaseOrder): bool
    {
        return DB::transaction(fn() => $this->purchaseOrders->delete($purchaseOrder));
    }

    public function updateStatus(PurchaseOrder $purchaseOrder, string $status): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $status) {
            $current = $purchaseOrder->status === 'pending approval' ? 'pending' : $purchaseOrder->status;
            $target = $status === 'pending approval' ? 'pending' : $status;
            $nextByCurrent = [
                'draft' => 'pending',
                'pending' => 'approved',
                'approved' => 'sent',
                'sent' => 'completed',
            ];

            if (($nextByCurrent[$current] ?? null) !== $target) {
                throw ValidationException::withMessages([
                    'status' => "Invalid status transition from {$current} to {$target}.",
                ]);
            }

            $payload = ['status' => $target];
            if ($target === 'approved') {
                $payload['approved_at'] = now();
            }

            $purchaseOrder = $this->purchaseOrders->update($purchaseOrder, $payload);

            if ($target === 'completed') {
                $this->finalizeCompletion($purchaseOrder->fresh(['items']));
            }

            return $purchaseOrder->fresh(['items', 'vendor', 'assets', 'series']);
        });
    }

    private function calculateTotal(array $items): float
    {
        return collect($items)->sum(fn($item) => ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 0));
    }

    private function syncLineItems(PurchaseOrder $po, array $items): void
    {
        foreach ($items as $item) {
            $vendorItemId = $item['vendor_item_id'] ?? null;

            if (!$vendorItemId && $po->vendor_id) {
                $vendorItem = VendorItem::firstOrCreate(
                    [
                        'vendor_id' => $po->vendor_id,
                        'name' => $item['name'],
                    ],
                    [
                        'description' => $item['description'] ?? null,
                        'unit' => $item['unit'] ?? null,
                        'unit_price' => $item['unit_price'],
                    ],
                );
                $vendorItemId = $vendorItem->id;
            }

            $subtotal = ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 0);

            $po->items()->create([
                'vendor_item_id' => $vendorItemId,
                'item_name' => $item['name'],
                'description' => $item['description'] ?? null,
                'unit' => $item['unit'] ?? null,
                'unit_price' => $item['unit_price'],
                'quantity' => $item['quantity'],
                'subtotal' => $subtotal,
            ]);
        }
    }

    private function syncAssets(PurchaseOrder $purchaseOrder): void
    {
        if ($purchaseOrder->procurement_type !== 'asset') {
            Asset::query()->where('purchase_order_id', $purchaseOrder->id)->delete();

            return;
        }

        $purchaseOrder->loadMissing('items');
        foreach ($purchaseOrder->items as $item) {
            Asset::query()->updateOrCreate(
                ['purchase_order_item_id' => $item->id],
                [
                    'purchase_order_id' => $purchaseOrder->id,
                    'name' => $item->item_name,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'acquisition_cost' => $item->subtotal,
                ]
            );
        }
    }

    private function finalizeCompletion(PurchaseOrder $purchaseOrder): void
    {
        $this->syncAssets($purchaseOrder);
        $this->createInvoiceFromPurchaseOrder($purchaseOrder);
        $this->createPayableJournal($purchaseOrder);
    }

    private function createInvoiceFromPurchaseOrder(PurchaseOrder $purchaseOrder): void
    {
        $invoice = Invoice::query()->firstOrCreate(
            [
                'related_type' => 'purchase_order',
                'related_id' => $purchaseOrder->id,
            ],
            [
                'company_id' => $purchaseOrder->company_id,
                'invoice_number' => $this->numberService->generate(Invoice::class),
                'issue_date' => $purchaseOrder->po_date ?? now()->toDateString(),
                'due_date' => Carbon::parse($purchaseOrder->po_date ?? now())->addDays(14)->toDateString(),
                'status' => 'issued',
                'total_amount' => $purchaseOrder->total_amount,
            ],
        );

        if ($invoice->details()->count() === 0) {
            $purchaseOrder->loadMissing('items');
            foreach ($purchaseOrder->items as $item) {
                $invoice->details()->create([
                    'description' => $item->item_name,
                    'unit' => $item->unit,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ]);
            }
        }
    }

    private function normalizeSeriesRelation(array $data): array
    {
        if (!empty($data['event_series_id'])) {
            $series = EventSeries::query()->find($data['event_series_id']);
            if ($series) {
                $data['event_id'] = $series->event_id;
            }
        }

        if (($data['procurement_type'] ?? null) === 'asset') {
            $data['event_series_id'] = null;
            $data['event_id'] = null;
        }

        return $data;
    }

    private function createPayableJournal(PurchaseOrder $purchaseOrder): void
    {
        $reference = "PO:{$purchaseOrder->po_number}";
        if (Journal::query()->where('reference', $reference)->exists()) {
            return;
        }

        $payableAccount = ChartOfAccount::query()
            ->where('type', 'liability')
            ->where(function ($query) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%payable%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%account payable%']);
            })
            ->first() ?? ChartOfAccount::query()->where('type', 'liability')->first();

        $debitType = $purchaseOrder->procurement_type === 'asset' ? 'asset' : 'expense';
        $debitAccount = ChartOfAccount::query()->where('type', $debitType)->first();

        if (!$payableAccount || !$debitAccount) {
            return;
        }

        $journal = Journal::query()->create([
            'journal_number' => $this->numberService->generate(Journal::class),
            'date' => $purchaseOrder->po_date ?? now()->toDateString(),
            'description' => "Purchase Order {$purchaseOrder->po_number} completion",
            'reference' => $reference,
            'status' => 'posted',
            'total_debit' => $purchaseOrder->total_amount,
            'total_credit' => $purchaseOrder->total_amount,
        ]);

        $journal->lines()->create([
            'account_id' => $debitAccount->id,
            'description' => "PO {$purchaseOrder->po_number}",
            'debit' => $purchaseOrder->total_amount,
            'credit' => 0,
        ]);

        $journal->lines()->create([
            'account_id' => $payableAccount->id,
            'description' => "PO {$purchaseOrder->po_number}",
            'debit' => 0,
            'credit' => $purchaseOrder->total_amount,
        ]);
    }
}
