<?php

namespace App\Services;

use App\Models\Team;
use App\Repositories\TeamRepository;

class TeamService
{
    public function __construct(private TeamRepository $teams) {}

    public function paginate(int $perPage = 15)
    {
        return $this->teams->paginate($perPage);
    }

    public function create(array $data): Team
    {
        return $this->teams->create($data);
    }

    public function update(Team $team, array $data): Team
    {
        return $this->teams->update($team, $data);
    }

    public function delete(Team $team): bool
    {
        return $this->teams->delete($team);
    }
}
