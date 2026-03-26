<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use App\Services\TeamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TeamController extends Controller
{
    public function __construct(private TeamService $teams) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Team::class);

        return TeamResource::collection(
            $this->teams->paginate($request->integer('per_page', 15))
        );
    }

    public function store(StoreTeamRequest $request)
    {
        $team = $this->teams->create($request->validated());

        return (new TeamResource($team->load(['leadEmployee', 'employees'])))->response()->setStatusCode(201);
    }

    public function show(Team $team)
    {
        Gate::authorize('view', $team);

        return new TeamResource($team->load(['leadEmployee', 'employees']));
    }

    public function update(UpdateTeamRequest $request, Team $team)
    {
        Gate::authorize('update', $team);

        return new TeamResource(
            $this->teams->update($team, $request->validated())->load(['leadEmployee', 'employees'])
        );
    }

    public function destroy(Team $team)
    {
        Gate::authorize('delete', $team);
        $this->teams->delete($team);

        return response()->noContent();
    }
}
