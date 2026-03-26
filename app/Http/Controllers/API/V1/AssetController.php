<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Asset\UpdateAssetRequest;
use App\Http\Resources\AssetResource;
use App\Models\Asset;
use App\Services\AssetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AssetController extends Controller
{
    public function __construct(private AssetService $assets) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Asset::class);

        $filters = $request->only(['purchase_order_id', 'name']);

        return AssetResource::collection(
            $this->assets->paginate($request->integer('per_page', 15), $filters)
        );
    }

    public function show(Asset $asset)
    {
        Gate::authorize('view', $asset);

        return new AssetResource($asset->load(['purchaseOrder.vendor', 'purchaseOrderItem']));
    }

    public function update(UpdateAssetRequest $request, Asset $asset)
    {
        Gate::authorize('update', $asset);

        return new AssetResource(
            $this->assets->update($asset, $request->validated())->load(['purchaseOrder.vendor', 'purchaseOrderItem'])
        );
    }
}
