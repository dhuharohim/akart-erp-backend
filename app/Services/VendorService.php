<?php

namespace App\Services;

use App\Models\Vendor;
use App\Repositories\VendorRepository;
use Illuminate\Support\Facades\DB;

class VendorService
{
    public function __construct(
        private VendorRepository $vendors,
    ) {}

    public function paginate(int $perPage = 15, array $filters = [])
    {
        return $this->vendors->paginate($perPage, $filters);
    }

    public function create(array $data): Vendor
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            if (empty($data['vendor_code'])) {
                $data['vendor_code'] = hash('sha256', \Illuminate\Support\Str::uuid()->toString() . microtime(true));
            }

            $vendor = $this->vendors->create($data);

            foreach ($items as $item) {
                $vendor->items()->create($item);
            }

            return $vendor;
        });
    }

    public function update(Vendor $vendor, array $data): Vendor
    {
        return DB::transaction(function () use ($vendor, $data) {
            $items = $data['items'] ?? null;
            unset($data['items']);

            $vendor = $this->vendors->update($vendor, $data);

            if ($items !== null) {
                $vendor->items()->delete();
                foreach ($items as $item) {
                    $vendor->items()->create($item);
                }
            }

            return $vendor;
        });
    }

    public function delete(Vendor $vendor): bool
    {
        return $this->vendors->delete($vendor);
    }
}
