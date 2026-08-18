<?php

namespace App\Policies;

use App\Enums\ApplicationStatus;
use App\Models\IndigeneApplication;
use App\Models\User;

class ApplicationPolicy
{
    use ChecksLgaScope;

    public function viewAny(User $user): bool
    {
        return $this->active($user) && $user->can('application.view');
    }

    public function view(User $user, IndigeneApplication $application): bool
    {
        return $this->active($user)
            && ($user->isSystemAdmin() || $this->sameLga($user, $application->lga_id));
    }

    public function create(User $user): bool
    {
        return $this->active($user) && $user->can('application.create');
    }

    public function update(User $user, IndigeneApplication $application): bool
    {
        if (! $this->active($user) || ! $this->sameLga($user, $application->lga_id)) {
            return false;
        }

        // Only drafts and correction states are editable.
        if (! $application->canBeSubmitted()) {
            return false;
        }

        if ($application->created_by === $user->id) {
            return true;
        }

        if ($user->isSystemAdmin()) {
            return true;
        }

        // Another user's draft may be edited when delegated (audited).
        return $application->assigned_reviewer_id === $user->id && $user->can('application.edit-delegated');
    }

    public function submit(User $user, IndigeneApplication $application): bool
    {
        return $this->active($user)
            && $application->canBeSubmitted()
            && ($application->created_by === $user->id || $user->isSystemAdmin())
            && $this->sameLga($user, $application->lga_id);
    }

    public function decide(User $user, IndigeneApplication $application): bool
    {
        return $this->active($user) && $application->canBeDecidedBy($user);
    }

    public function reviewDuplicates(User $user): bool
    {
        return $this->active($user) && $user->can('application.review-duplicates');
    }

    public function resolveDuplicate(User $user, IndigeneApplication $application): bool
    {
        return $this->active($user)
            && $user->can('application.review-duplicates')
            && ($user->isSystemAdmin() || $this->sameLga($user, $application->lga_id));
    }

    private function active(User $user): bool
    {
        return $user->isActive() && ! $user->must_change_password;
    }
}
