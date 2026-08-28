<?php

namespace App\Services;

use App\Enums\CertificateStatus;
use App\Models\Certificate;
use App\Models\CertificatePrintEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * SRD 15.3 - idempotent print occurrence accounting.
 * Each server-authorised printable copy creates exactly one print event.
 */
class PrintEventService
{
    public function __construct(private AuditService $audit) {}

    public function createPrintEvent(Certificate $certificate, User $user, string $idempotencyKey, ?string $reasonCode = null, ?string $reasonNote = null): CertificatePrintEvent
    {
        if ($certificate->status !== CertificateStatus::Active) {
            throw new HttpException(409, 'This certificate cannot be printed in its current status.');
        }

        $idempotencyHash = hash('sha256', $user->id.'|'.$idempotencyKey);

        // Idempotency: a retry or double click returns the existing event instead of double counting.
        $existing = CertificatePrintEvent::where('requested_by', $user->id)
            ->where('idempotency_key_hash', $idempotencyHash)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($certificate, $user, $idempotencyHash, $reasonCode, $reasonNote) {
            $nextNumber = CertificatePrintEvent::where('certificate_id', $certificate->id)
                ->lockForUpdate()
                ->max('print_number') + 1;

            $event = CertificatePrintEvent::create([
                'certificate_id' => $certificate->id,
                'certificate_version_id' => $certificate->current_version_id,
                'print_number' => $nextNumber,
                'copy_type' => $nextNumber === 1 ? 'original' : 'reprint',
                'reason_code' => $reasonCode,
                'reason_note' => $reasonNote,
                'requested_by' => $user->id,
                'requester_role' => $user->roles()->first()?->name,
                'requester_lga_id' => $user->activeLga()?->id,
                'idempotency_key_hash' => $idempotencyHash,
                'ip_hash' => request() ? hash('sha256', request()->ip().'|'.config('app.key')) : null,
                'user_agent' => request()?->userAgent(),
            ]);

            $certificate->total_prints_cached = $nextNumber;
            $certificate->save();

            $this->audit->record('certificate.printed', Certificate::class, $certificate->id, [], [
                'print_number' => $nextNumber,
                'copy_type' => $event->copy_type,
            ], 'medium', $user);

            return $event;
        });
    }

    /**
     * Real-time rendering: the certificate PDF is produced on the fly from the
     * immutable version snapshot and returned to the browser. Nothing is stored
     * on disk; the print event row remains the single source of count truth.
     */
    public function renderRealtime(Certificate $certificate, CertificatePrintEvent $event): string
    {
        $version = $event->version ?? $certificate->currentVersion;

        if (! $version) {
            throw new HttpException(409, 'No issued version exists for this certificate.');
        }

        $snapshot = json_decode(\Illuminate\Support\Facades\Crypt::decryptString($version->snapshot_ciphertext), true);

        return app(CertificateRenderService::class)->renderPdf($snapshot, null, $event->copyLabel());
    }
}
