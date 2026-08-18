<?php

namespace App\Services;

use App\Enums\CertificateStatus;
use App\Enums\LifecycleStatus;
use App\Models\Certificate;
use App\Models\CertificateStatusEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * SRD 15 / 23.4 - suspend, reinstate, revoke and supersede with reason and authority.
 * Revocation is not deletion.
 */
class CertificateStatusService
{
    public function __construct(private AuditService $audit) {}

    public function suspend(Certificate $certificate, User $user, string $reasonCode, string $reasonText): void
    {
        if ($certificate->status !== CertificateStatus::Active) {
            throw new HttpException(409, 'Only an active certificate can be suspended.');
        }

        DB::transaction(function () use ($certificate, $user, $reasonCode, $reasonText) {
            $from = $certificate->status->value;

            $certificate->status = CertificateStatus::Suspended;
            $certificate->save();

            $this->event($certificate, $from, CertificateStatus::Suspended->value, $reasonCode, $reasonText, $user);
            $this->audit->record('certificate.suspended', Certificate::class, $certificate->id,
                ['status' => $from], ['status' => CertificateStatus::Suspended->value, 'reason' => $reasonCode], 'high', $user);
        });
    }

    public function reinstate(Certificate $certificate, User $user, string $reasonCode, string $reasonText): void
    {
        if ($certificate->status !== CertificateStatus::Suspended) {
            throw new HttpException(409, 'Only a suspended certificate can be reinstated.');
        }

        DB::transaction(function () use ($certificate, $user, $reasonCode, $reasonText) {
            $from = $certificate->status->value;

            $certificate->status = CertificateStatus::Active;
            $certificate->save();

            $this->event($certificate, $from, CertificateStatus::Active->value, $reasonCode, $reasonText, $user);
            $this->audit->record('certificate.reinstated', Certificate::class, $certificate->id,
                ['status' => $from], ['status' => CertificateStatus::Active->value], 'high', $user);
        });
    }

    public function revoke(Certificate $certificate, User $user, string $reasonCode, string $reasonText): void
    {
        if (! in_array($certificate->status, [CertificateStatus::Active, CertificateStatus::Suspended, CertificateStatus::Eligible], true)) {
            throw new HttpException(409, 'This certificate cannot be revoked from its current status.');
        }

        DB::transaction(function () use ($certificate, $user, $reasonCode, $reasonText) {
            $from = $certificate->status->value;

            $certificate->status = CertificateStatus::Revoked;
            $certificate->save();

            $indigene = $certificate->indigene;
            $indigene->lifecycle_status = LifecycleStatus::Revoked->value;
            $indigene->revoked_at = now();
            $indigene->save();

            $this->event($certificate, $from, CertificateStatus::Revoked->value, $reasonCode, $reasonText, $user);
            $this->audit->record('certificate.revoked', Certificate::class, $certificate->id,
                ['status' => $from], ['status' => CertificateStatus::Revoked->value, 'reason' => $reasonCode], 'high', $user);
        });
    }

    private function event(Certificate $certificate, string $from, string $to, string $reasonCode, string $reasonText, User $user): void
    {
        CertificateStatusEvent::create([
            'certificate_id' => $certificate->id,
            'from_status' => $from,
            'to_status' => $to,
            'reason_code' => $reasonCode,
            'reason_text' => $reasonText,
            'effective_at' => now(),
            'actor_id' => $user->id,
            'actor_role' => $user->roles()->first()?->name,
            'actor_lga_id' => $user->activeLga()?->id,
        ]);
    }
}
