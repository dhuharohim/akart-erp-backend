<?php

namespace App\Repositories;

use App\Models\Sponsor;

class SponsorRepository extends BaseRepository
{
    public function __construct(Sponsor $sponsor)
    {
        parent::__construct($sponsor);
    }

    public function paginate(int $perPage = 15, array $filters = [])
    {
        $query = $this->query();

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%'.$filters['name'].'%');
        }

        return $query->latest()->paginate($perPage);
    }
}
