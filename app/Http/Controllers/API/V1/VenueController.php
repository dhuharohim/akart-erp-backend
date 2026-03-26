<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Venue\StoreVenueRequest;
use App\Http\Requests\Venue\UpdateVenueRequest;
use App\Http\Resources\VenueResource;
use App\Models\Venue;
use App\Services\VenueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VenueController extends Controller
{
    public function __construct(private VenueService $venues) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Venue::class);

        $filters = $request->only(['search', 'type']);

        return VenueResource::collection(
            $this->venues->paginate($request->integer('per_page', 15), $filters)
        );
    }

    public function store(StoreVenueRequest $request)
    {
        $venue = $this->venues->create($request->validated());

        return (new VenueResource($venue))->response()->setStatusCode(201);
    }

    public function show(Venue $venue)
    {
        Gate::authorize('view', $venue);

        return new VenueResource($venue->load('facilities'));
    }

    public function update(UpdateVenueRequest $request, Venue $venue)
    {
        $venue = $this->venues->update($venue, $request->validated());

        return new VenueResource($venue);
    }

    public function destroy(Venue $venue)
    {
        Gate::authorize('delete', $venue);
        $this->venues->delete($venue);

        return response()->noContent();
    }

    public function all()
    {
        Gate::authorize('viewAny', Venue::class);

        return VenueResource::collection($this->venues->all());
    }
}
