<?php

namespace App\Services;

use App\Models\Sponsor;
use App\Repositories\SponsorRepository;
use Illuminate\Support\Str;

class SponsorService
{
    public function __construct(private SponsorRepository $sponsors) {}

    public function paginate(int $perPage = 15, array $filters = [])
    {
        return $this->sponsors->paginate($perPage, $filters);
    }

    public function create(array $data): Sponsor
    {
        $data['sponsor_code'] = hash('sha256', Str::uuid()->toString() . microtime(true));

        return $this->sponsors->create($data);
    }

    public function update(Sponsor $sponsor, array $data): Sponsor
    {
        unset($data['sponsor_code']);
        if (empty($sponsor->sponsor_code)) {
            $data['sponsor_code'] = hash('sha256', Str::uuid()->toString() . microtime(true));
        }

        return $this->sponsors->update($sponsor, $data);
    }

    public function delete(Sponsor $sponsor): bool
    {
        return $this->sponsors->delete($sponsor);
    }
}
