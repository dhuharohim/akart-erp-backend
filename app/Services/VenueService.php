<?php

namespace App\Services;

use App\Models\Venue;
use App\Repositories\VenueRepository;
use Illuminate\Support\Facades\DB;

class VenueService
{
    public function __construct(
        private VenueRepository $venues,
        private DocumentNumberService $documentNumber,
    ) {}

    public function paginate(int $perPage = 15, array $filters = [])
    {
        return $this->venues->paginate($perPage, $filters);
    }

    public function create(array $data): Venue
    {
        $facilities = $data['facilities'] ?? [];
        unset($data['facilities']);

        return DB::transaction(function () use ($data, $facilities) {
            $data['code'] = $this->documentNumber->generate(Venue::class);
            $venue = $this->venues->create($data);

            foreach ($facilities as $facility) {
                $venue->facilities()->create($facility);
            }

            return $venue->load('facilities');
        });
    }

    public function update(Venue $venue, array $data): Venue
    {
        $facilities = $data['facilities'] ?? null;
        unset($data['facilities']);

        return DB::transaction(function () use ($venue, $data, $facilities) {
            $venue = $this->venues->update($venue, $data);

            if ($facilities !== null) {
                $venue->facilities()->delete();
                foreach ($facilities as $facility) {
                    $venue->facilities()->create($facility);
                }
            }

            return $venue->load('facilities');
        });
    }

    public function delete(Venue $venue): bool
    {
        return $this->venues->delete($venue);
    }

    public function all()
    {
        return $this->venues->all();
    }
}
