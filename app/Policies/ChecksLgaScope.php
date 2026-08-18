<?php

namespace App\Policies;

use App\Models\Lga;
use App\Models\User;

trait ChecksLgaScope
{
    protected function sameLga(User $user, ?string $lgaId): bool
    {
        if ($user->isSystemAdmin()) {
            return true;
        }

        $assigned = $user->activeLga();

        return $assigned !== null && $assigned->id === $lgaId;
    }

    protected function lga(User $user): ?Lga
    {
        return $user->activeLga();
    }
}
