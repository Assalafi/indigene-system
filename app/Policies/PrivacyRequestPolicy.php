<?php

namespace App\Policies;

use App\Models\PrivacyRequest;
use App\Models\User;

class PrivacyRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive() && ($user->can('privacy.manage') || $user->can('privacy.view'));
    }

    public function view(User $user, PrivacyRequest $privacyRequest): bool
    {
        return $this->viewAny($user);
    }

    public function decide(User $user, PrivacyRequest $privacyRequest): bool
    {
        return $user->isActive() && $user->can('privacy.manage');
    }
}
