<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseOrder\StorePurchaseOrderRequest;
use App\Http\Requests\PurchaseOrder\UpdatePurchaseOrderRequest;
use App\Http\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PurchaseOrderController extends Controller
{
    public function __construct(private PurchaseOrderService $purchaseOrders) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', PurchaseOrder::class);

        $filters = $request->only(['po_number', 'vendor_id', 'event_id', 'event_series_id', 'status', 'procurement_type', 'po_date']);

        return PurchaseOrderResource::collection(
            $this->purchaseOrders->paginate($request->integer('per_page', 15), $filters)
        );
    }

    public function store(StorePurchaseOrderRequest $request)
    {
        $purchaseOrder = $this->purchaseOrders->create($request->validated());

        return (new PurchaseOrderResource($purchaseOrder))->response()->setStatusCode(201);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        Gate::authorize('view', $purchaseOrder);

        return new PurchaseOrderResource($purchaseOrder->load(['items', 'vendor', 'assets', 'series']));
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder)
    {
        return new PurchaseOrderResource($this->purchaseOrders->update($purchaseOrder, $request->validated()));
    }

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        Gate::authorize('update', $purchaseOrder);
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,sent,completed'],
        ]);

        return new PurchaseOrderResource(
            $this->purchaseOrders->updateStatus($purchaseOrder, $validated['status'])
        );
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        Gate::authorize('delete', $purchaseOrder);
        $this->purchaseOrders->delete($purchaseOrder);

        return response()->noContent();
    }
}
