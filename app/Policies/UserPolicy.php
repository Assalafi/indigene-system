<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive() && $user->can('user.manage');
    }

    public function view(User $user, User $model): bool
    {
        return $user->isActive() && ($user->can('user.manage') || $user->id === $model->id);
    }

    public function create(User $user): bool
    {
        return $user->isActive() && $user->can('user.manage');
    }

    public function update(User $user, User $model): bool
    {
        return $user->isActive() && $user->can('user.manage');
    }

    public function delete(User $user, User $model): bool
    {
        // FR-USR-006: the final active System Admin cannot be removed.
        if ($model->isSystemAdmin() && User::where('status', 'active')->get()->filter->isSystemAdmin()->count() <= 1) {
            return false;
        }

        return $user->isActive() && $user->can('user.manage');
    }
}
