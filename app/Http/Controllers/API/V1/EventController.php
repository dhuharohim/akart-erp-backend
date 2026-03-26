<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    public function __construct(private EventService $events) {}

    public function index()
    {
        Gate::authorize('viewAny', Event::class);

        return EventResource::collection($this->events->paginate());
    }

    public function store(StoreEventRequest $request)
    {
        $event = $this->events->create($request->validated());

        return (new EventResource($event))->response()->setStatusCode(201);
    }

    public function show(Event $event)
    {
        Gate::authorize('view', $event);

        return new EventResource($event);
    }

    public function update(UpdateEventRequest $request, Event $event)
    {
        Gate::authorize('update', $event);

        return new EventResource($this->events->update($event, $request->validated()));
    }

    public function destroy(Event $event)
    {
        Gate::authorize('delete', $event);
        $this->events->delete($event);

        return response()->noContent();
    }
}
