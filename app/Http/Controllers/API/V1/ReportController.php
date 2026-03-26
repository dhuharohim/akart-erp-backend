<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Services\DocumentService;
use App\Services\FinanceService;

class ReportController extends Controller
{
    public function __construct(
        private FinanceService $finance,
        private DocumentService $documents
    ) {}

    public function kpis()
    {
        $eventsCount = Event::query()->count();
        $completedEvents = Event::query()->where('status', 'completed')->count();
        $profitSum = Event::query()->sum('profit_amount');

        return response()->json([
            'events_total' => $eventsCount,
            'events_completed' => $completedEvents,
            'profit_total' => (float) $profitSum,
            'payments' => $this->finance->paymentSummary(),
        ]);
    }

    public function monthlyRevenue()
    {
        return response()->json([
            'data' => $this->finance->monthlyRevenue(),
        ]);
    }

    public function eventProfitability()
    {
        $data = Event::query()
            ->select(['id', 'event_number', 'name', 'revenue_amount', 'expense_amount', 'profit_amount'])
            ->orderByDesc('profit_amount')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function invoicePdf(Invoice $invoice)
    {
        $document = $this->documents->generatePdf([
            'document_number' => $invoice->invoice_number,
            'related_type' => Invoice::class,
            'related_id' => $invoice->id,
            'invoice' => $invoice,
        ], 'pdf.invoice', request()->user()->id);

        return response()->json(['data' => $document]);
    }

    public function purchaseOrderPdf(PurchaseOrder $purchaseOrder)
    {
        $document = $this->documents->generatePdf([
            'document_number' => $purchaseOrder->po_number,
            'related_type' => PurchaseOrder::class,
            'related_id' => $purchaseOrder->id,
            'purchase_order' => $purchaseOrder,
        ], 'pdf.purchase-order', request()->user()->id);

        return response()->json(['data' => $document]);
    }
}
