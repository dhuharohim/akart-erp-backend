<?php

namespace App\Policies;

use App\Models\Sponsor;
use App\Models\User;

class SponsorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('sponsors.view');
    }

    public function view(User $user, Sponsor $sponsor): bool
    {
        return $user->can('sponsors.view');
    }

    public function create(User $user): bool
    {
        return $user->can('sponsors.create');
    }

    public function update(User $user, Sponsor $sponsor): bool
    {
        return $user->can('sponsors.update');
    }

    public function delete(User $user, Sponsor $sponsor): bool
    {
        return $user->can('sponsors.delete');
    }
}
