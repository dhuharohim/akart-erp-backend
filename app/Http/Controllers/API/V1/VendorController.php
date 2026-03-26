<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\StoreVendorRequest;
use App\Http\Requests\Vendor\UpdateVendorRequest;
use App\Http\Resources\VendorItemResource;
use App\Http\Resources\VendorResource;
use App\Models\Vendor;
use App\Models\VendorItem;
use App\Services\VendorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VendorController extends Controller
{
    public function __construct(private VendorService $vendors) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Vendor::class);

        $filters = $request->only(['vendor_code', 'name', 'email', 'phone']);

        return VendorResource::collection(
            $this->vendors->paginate($request->integer('per_page', 15), $filters)
        );
    }

    public function store(StoreVendorRequest $request)
    {
        $vendor = $this->vendors->create($request->validated());

        return (new VendorResource($vendor->load('items')))->response()->setStatusCode(201);
    }

    public function show(Vendor $vendor)
    {
        Gate::authorize('view', $vendor);

        return new VendorResource($vendor->load('items'));
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor)
    {
        $vendor = $this->vendors->update($vendor, $request->validated());

        return new VendorResource($vendor->load('items'));
    }

    public function destroy(Vendor $vendor)
    {
        Gate::authorize('delete', $vendor);
        $this->vendors->delete($vendor);

        return response()->noContent();
    }

    // --- Vendor Items Sub-Resource ---

    public function itemsIndex(Vendor $vendor)
    {
        Gate::authorize('view', $vendor);

        return VendorItemResource::collection($vendor->items);
    }

    public function itemsStore(Request $request, Vendor $vendor)
    {
        Gate::authorize('update', $vendor);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:50'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $item = $vendor->items()->create($validated);

        return (new VendorItemResource($item))->response()->setStatusCode(201);
    }

    public function itemsUpdate(Request $request, Vendor $vendor, VendorItem $item)
    {
        Gate::authorize('update', $vendor);

        abort_if($item->vendor_id !== $vendor->id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:50'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $item->update($validated);

        return new VendorItemResource($item);
    }

    public function itemsDestroy(Vendor $vendor, VendorItem $item)
    {
        Gate::authorize('update', $vendor);

        abort_if($item->vendor_id !== $vendor->id, 404);

        $item->delete();

        return response()->noContent();
    }
}
