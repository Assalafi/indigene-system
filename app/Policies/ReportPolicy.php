<?php

namespace App\Policies;

use App\Models\ReportExport;
use App\Models\User;

class ReportPolicy
{
    use ChecksLgaScope;

    public function viewAny(User $user): bool
    {
        return $user->isActive() && $user->can('report.view');
    }

    public function export(User $user): bool
    {
        return $user->isActive() && $user->can('report.export');
    }

    public function viewExport(User $user, ReportExport $export): bool
    {
        return $user->isActive()
            && ($user->isSystemAdmin() || $export->requested_by === $user->id);
    }
}
