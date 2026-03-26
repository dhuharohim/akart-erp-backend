<?php

namespace App\Policies;

use App\Models\ChartOfAccount;
use App\Models\User;

class ChartOfAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('coa.view');
    }

    public function view(User $user, ChartOfAccount $account): bool
    {
        return $user->can('coa.view');
    }

    public function create(User $user): bool
    {
        return $user->can('coa.create');
    }

    public function update(User $user, ChartOfAccount $account): bool
    {
        return $user->can('coa.update');
    }

    public function delete(User $user, ChartOfAccount $account): bool
    {
        return $user->can('coa.delete');
    }
}
