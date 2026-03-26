<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sponsor\StoreSponsorRequest;
use App\Http\Requests\Sponsor\UpdateSponsorRequest;
use App\Http\Resources\SponsorResource;
use App\Models\Sponsor;
use App\Services\SponsorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SponsorController extends Controller
{
    public function __construct(private SponsorService $sponsors) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Sponsor::class);
        $filters = $request->only(['name']);

        return SponsorResource::collection(
            $this->sponsors->paginate($request->integer('per_page', 15), $filters)
        );
    }

    public function store(StoreSponsorRequest $request)
    {
        return (new SponsorResource($this->sponsors->create($request->validated())))->response()->setStatusCode(201);
    }

    public function show(Sponsor $sponsor)
    {
        Gate::authorize('view', $sponsor);

        return new SponsorResource($sponsor);
    }

    public function update(UpdateSponsorRequest $request, Sponsor $sponsor)
    {
        Gate::authorize('update', $sponsor);

        return new SponsorResource($this->sponsors->update($sponsor, $request->validated()));
    }

    public function destroy(Sponsor $sponsor)
    {
        Gate::authorize('delete', $sponsor);
        $this->sponsors->delete($sponsor);

        return response()->noContent();
    }
}
