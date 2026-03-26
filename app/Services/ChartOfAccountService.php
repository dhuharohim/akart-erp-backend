<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Repositories\ChartOfAccountRepository;

class ChartOfAccountService
{
    public function __construct(
        private ChartOfAccountRepository $accounts,
    ) {}

    public function paginate(int $perPage = 15, array $filters = [])
    {
        return $this->accounts->paginate($perPage, $filters);
    }

    public function create(array $data): ChartOfAccount
    {
        return $this->accounts->create($data);
    }

    public function update(ChartOfAccount $account, array $data): ChartOfAccount
    {
        return $this->accounts->update($account, $data);
    }

    public function delete(ChartOfAccount $account): bool
    {
        return $this->accounts->delete($account);
    }
}
