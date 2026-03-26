<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChartOfAccount\StoreChartOfAccountRequest;
use App\Http\Requests\ChartOfAccount\UpdateChartOfAccountRequest;
use App\Http\Resources\ChartOfAccountResource;
use App\Models\ChartOfAccount;
use App\Services\ChartOfAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ChartOfAccountController extends Controller
{
    public function __construct(private ChartOfAccountService $accounts) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', ChartOfAccount::class);

        $filters = $request->only(['code', 'name', 'type']);

        return ChartOfAccountResource::collection(
            $this->accounts->paginate($request->integer('per_page', 15), $filters)
        );
    }

    public function store(StoreChartOfAccountRequest $request)
    {
        $account = $this->accounts->create($request->validated());

        return (new ChartOfAccountResource($account))->response()->setStatusCode(201);
    }

    public function show(ChartOfAccount $chartOfAccount)
    {
        Gate::authorize('view', $chartOfAccount);

        return new ChartOfAccountResource($chartOfAccount->load('children'));
    }

    public function update(UpdateChartOfAccountRequest $request, ChartOfAccount $chartOfAccount)
    {
        return new ChartOfAccountResource($this->accounts->update($chartOfAccount, $request->validated()));
    }

    public function destroy(ChartOfAccount $chartOfAccount)
    {
        Gate::authorize('delete', $chartOfAccount);
        $this->accounts->delete($chartOfAccount);

        return response()->noContent();
    }
}
