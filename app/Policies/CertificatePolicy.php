<?php

namespace App\Policies;

use App\Models\Certificate;
use App\Models\User;

class CertificatePolicy
{
    use ChecksLgaScope;

    public function viewAny(User $user): bool
    {
        return $user->isActive() && $user->can('certificate.view');
    }

    public function view(User $user, Certificate $certificate): bool
    {
        return $user->isActive()
            && ($user->isSystemAdmin() || $this->sameLga($user, $certificate->lga_id));
    }

    public function issue(User $user, Certificate $certificate): bool
    {
        return $this->view($user, $certificate) && $user->can('certificate.issue');
    }

    public function changeStatus(User $user, Certificate $certificate): bool
    {
        return $this->view($user, $certificate) && $user->can('certificate.manage-status');
    }

    public function viewPrintHistory(User $user): bool
    {
        return $user->isActive() && $user->can('certificate.view');
    }
}
