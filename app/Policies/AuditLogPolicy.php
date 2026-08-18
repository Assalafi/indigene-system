<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive()
            && ($user->can('audit.view') || $user->can('audit.view-lga') || $user->can('audit.view-own'));
    }

    public function view(User $user, AuditLog $log): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ($user->isSystemAdmin()) {
            return true;
        }

        // LGA roles see own-LGA action log; officers see own actions.
        if ($user->can('audit.view-lga')) {
            return $user->activeLga()?->id === $log->actor_lga_id;
        }

        return $log->actor_id === $user->id;
    }
}
