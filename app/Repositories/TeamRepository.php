<?php

namespace App\Repositories;

use App\Models\Team;

class TeamRepository extends BaseRepository
{
    public function __construct(Team $team)
    {
        parent::__construct($team);
    }

    public function paginate(int $perPage = 15)
    {
        return $this->query()
            ->with(['leadEmployee:id,full_name', 'employees:id,team_id'])
            ->withCount('employees')
            ->latest()
            ->paginate($perPage);
    }
}
