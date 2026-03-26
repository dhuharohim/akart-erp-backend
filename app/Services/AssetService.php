<?php

namespace App\Services;

use App\Models\Asset;
use App\Repositories\AssetRepository;

class AssetService
{
    public function __construct(private AssetRepository $assets) {}

    public function paginate(int $perPage = 15, array $filters = [])
    {
        return $this->assets->paginate($perPage, $filters);
    }

    public function update(Asset $asset, array $data): Asset
    {
        return $this->assets->update($asset, $data);
    }
}
