<?php

namespace App\Repositories;

use App\Models\Event;

class EventRepository extends BaseRepository
{
    public function __construct(Event $event)
    {
        parent::__construct($event);
    }
}
