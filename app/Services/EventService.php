<?php

namespace App\Services;

use App\Models\Event;
use App\Repositories\EventRepository;
use Illuminate\Support\Facades\DB;

class EventService
{
    public function __construct(
        private EventRepository $events,
        private DocumentNumberService $documentNumbers,
    ) {}

    public function paginate(int $perPage = 15)
    {
        return $this->events->paginate($perPage);
    }

    public function create(array $data): Event
    {
        return DB::transaction(function () use ($data) {
            $data['event_number'] = $this->documentNumbers->generate(Event::class);
            return $this->events->create($data);
        });
    }

    public function update(Event $event, array $data): Event
    {
        return $this->events->update($event, $data);
    }

    public function delete(Event $event): bool
    {
        return $this->events->delete($event);
    }
}
