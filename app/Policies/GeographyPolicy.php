<?php

namespace App\Policies;

use App\Models\District;
use App\Models\Lga;
use App\Models\State;
use App\Models\Unit;
use App\Models\User;
use App\Models\Ward;

class GeographyPolicy
{
    use ChecksLgaScope;

    public function viewAny(User $user): bool
    {
        return $user->isActive()
            && ($user->can('geography.view') || $user->can('geography.manage-local'));
    }

    public function manageNational(User $user): bool
    {
        return $user->isActive() && $user->isSystemAdmin() && $user->can('geography.manage-national');
    }

    public function manageLocal(User $user, Lga $lga): bool
    {
        return $user->isActive()
            && $user->can('geography.manage-local')
            && $this->sameLga($user, $lga->id);
    }

    public function import(User $user): bool
    {
        return $user->isActive() && $user->isSystemAdmin() && $user->can('geography.import');
    }

    public function viewLocal(User $user, Lga $lga): bool
    {
        return $user->isActive()
            && ($user->isSystemAdmin() || $this->sameLga($user, $lga->id));
    }
}
