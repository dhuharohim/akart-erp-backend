<?php

namespace App\Policies;

use App\Models\Journal;
use App\Models\User;

class JournalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('journals.view');
    }

    public function view(User $user, Journal $journal): bool
    {
        return $user->can('journals.view');
    }

    public function create(User $user): bool
    {
        return $user->can('journals.create');
    }

    public function update(User $user, Journal $journal): bool
    {
        return $user->can('journals.update');
    }

    public function delete(User $user, Journal $journal): bool
    {
        return $user->can('journals.delete');
    }
}
