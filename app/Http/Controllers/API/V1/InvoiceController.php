<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Gate;

use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $invoices) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Invoice::class);

        $filters = $request->only(['related_type', 'related_id', 'invoice_number']);

        return InvoiceResource::collection(
            $this->invoices->paginate($request->integer('per_page', 15), $filters)
        );
    }

    public function store(StoreInvoiceRequest $request)
    {
        $invoice = $this->invoices->create($request->validated());

        return (new InvoiceResource($invoice->load('details')))->response()->setStatusCode(201);
    }

    public function show(Invoice $invoice)
    {
        Gate::authorize('view', $invoice);

        return new InvoiceResource($invoice->load('details'));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        Gate::authorize('update', $invoice);

        return new InvoiceResource($this->invoices->update($invoice, $request->validated())->load('details'));
    }

    public function destroy(Invoice $invoice)
    {
        Gate::authorize('delete', $invoice);
        $this->invoices->delete($invoice);

        return response()->noContent();
    }
}
