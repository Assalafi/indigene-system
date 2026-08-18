<?php

namespace App\Policies;

use App\Enums\CertificateStatus;
use App\Models\Certificate;
use App\Models\User;

/**
 * SRD 15.1 - certificate print eligibility.
 */
class CertificatePrintPolicy
{
    use ChecksLgaScope;

    public function print(User $user, Certificate $certificate): bool
    {
        return $user->isActive()
            && $user->can('certificate.print')
            && ($user->isSystemAdmin() || $this->sameLga($user, $certificate->lga_id))
            && $certificate->status === CertificateStatus::Active
            && $certificate->indigene->lifecycle_status === 'active'
            // SRD 45.3: only an unresolved exact NIN flag blocks; advisory fuzzy flags never block.
            && ! $certificate->indigene->applications()
                ->where('status', 'approved')
                ->whereHas('duplicateFlags', fn ($q) => $q
                    ->where('status', 'open')
                    ->where('match_type', 'nin_exact'))
                ->exists()
            && $certificate->lga->activeSignatory()->exists()
            && $certificate->lga->profile()->exists();
    }
}
