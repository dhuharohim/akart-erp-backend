<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\EventRegistration;
use App\Services\XenditService;
use Illuminate\Http\Request;

class XenditInvoiceController extends Controller
{
    public function __construct(private XenditService $xendit) {}

    public function show(string $invoiceId)
    {
        $invoice = $this->xendit->getInvoice($invoiceId);

        abort_if(! $invoice, 404, 'Invoice not found on Xendit.');

        return response()->json(['data' => $invoice]);
    }

    public function index(Request $request)
    {
        $params = $request->only([
            'statuses',
            'limit',
            'created_after',
            'created_before',
            'paid_after',
            'paid_before',
            'expired_after',
            'expired_before',
            'last_invoice_id',
            'client_type',
            'payment_channels',
            'on_demand_link',
            'recurring_payment_id',
        ]);

        $invoices = $this->xendit->listInvoices($params);

        return response()->json(['data' => $invoices]);
    }

    public function showPublic(string $invoiceId)
    {
        $invoice = $this->xendit->getInvoice($invoiceId);

        abort_if(! $invoice, 404, 'Invoice not found.');

        // Attach event info from linked registration
        $eventInfo = null;
        $externalId = $invoice['external_id'] ?? null;
        if ($externalId) {
            $registration = EventRegistration::where('xendit_external_id', $externalId)
                ->with(['series.event', 'category'])
                ->first();
            if ($registration) {
                $eventInfo = [
                    'event_name' => $registration->series?->event?->name,
                    'series_name' => $registration->series?->name,
                    'category_name' => $registration->category?->name,
                    'registration_number' => $registration->registration_number,
                    'attendee_name' => trim($registration->first_name.' '.$registration->last_name),
                ];
            }
        }

        return response()->json([
            'data' => $invoice,
            'event' => $eventInfo,
        ]);
    }

    public function expire(string $invoiceId)
    {
        $invoice = $this->xendit->expireInvoice($invoiceId);

        abort_if(! $invoice, 404, 'Failed to expire invoice.');

        return response()->json(['data' => $invoice]);
    }
}
