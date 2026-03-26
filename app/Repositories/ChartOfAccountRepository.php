<?php

namespace App\Repositories;

use App\Models\ChartOfAccount;

class ChartOfAccountRepository extends BaseRepository
{
    public function __construct(ChartOfAccount $model)
    {
        parent::__construct($model);
    }
}
