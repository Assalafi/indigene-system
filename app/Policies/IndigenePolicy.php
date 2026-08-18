<?php

namespace App\Policies;

use App\Models\Indigene;
use App\Models\User;

class IndigenePolicy
{
    use ChecksLgaScope;

    public function viewAny(User $user): bool
    {
        return $user->isActive() && $user->can('indigene.view');
    }

    public function view(User $user, Indigene $indigene): bool
    {
        return $user->isActive()
            && ($user->isSystemAdmin() || $this->sameLga($user, $indigene->origin_lga_id));
    }

    public function amend(User $user, Indigene $indigene): bool
    {
        return $this->view($user, $indigene)
            && $indigene->lifecycle_status === 'active'
            && $user->can('indigene.amend');
    }

    public function revealNin(User $user, Indigene $indigene): bool
    {
        return $user->isActive()
            && $user->can('indigene.reveal-nin')
            && ($user->isSystemAdmin() || $this->sameLga($user, $indigene->origin_lga_id));
    }

    public function suspend(User $user, Indigene $indigene): bool
    {
        return $this->view($user, $indigene) && $user->can('indigene.suspend');
    }
}
