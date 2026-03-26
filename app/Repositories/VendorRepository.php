<?php

namespace App\Repositories;

use App\Models\Vendor;

class VendorRepository extends BaseRepository
{
    public function __construct(Vendor $vendor)
    {
        parent::__construct($vendor);
    }
}
