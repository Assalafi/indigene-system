<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\CertificateVerificationEvent;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\RateLimiter;

/**
 * SRD 15.4 - public verification by certificate number or QR token.
 * Privacy-minimised: never reveals NIN, phone, address, kin or documents.
 */
class CertificateVerificationService
{
    public function verifyByNumber(string $number): ?array
    {
        $number = strtoupper(trim($number));

        $certificate = Certificate::with(['indigene.currentProfile', 'lga.state'])
            ->where('certificate_number', $number)
            ->first();

        $this->recordLookup($certificate, 'number', hash('sha256', $number));

        if (! $certificate) {
            return null;
        }

        return $this->present($certificate);
    }

    public function verifyByToken(string $token): ?array
    {
        $tokenHash = hash('sha256', $token);

        $certificate = Certificate::with(['indigene.currentProfile', 'lga.state'])
            ->where('public_token_hash', $tokenHash)
            ->first();

        $this->recordLookup($certificate, 'token', $tokenHash);

        if (! $certificate) {
            return null;
        }

        return $this->present($certificate);
    }

    private function present(Certificate $certificate): array
    {
        $profile = $certificate->indigene->currentProfile;
        $version = $certificate->currentVersion;
        $status = $certificate->status->verificationLabel();

        $result = [
            'status' => $status,
            'certificate_number' => $certificate->certificate_number,
            'holder_name' => $profile?->fullName(),
            'issuing_lga' => $certificate->lga->name,
            'issuing_state' => $certificate->lga->state->name,
            'ward' => $profile?->ward?->name,
            'unit' => $profile?->unit?->name,
            'issue_date' => optional($certificate->issued_at)->format('d/m/Y'),
            'last_status_update' => optional($certificate->updated_at)->format('d/m/Y'),
        ];

        // Holder photograph appears only when policy permits (default off).
        if (SystemSetting::getSetting('public_verification_show_photo', '0') === '1') {
            $result['photo_url'] = $profile?->photoFile
                ? route('public.photo', ['file' => $profile->photoFile->id])
                : null;
        }

        return $result;
    }

    private function recordLookup(?Certificate $certificate, string $type, string $hash): void
    {
        try {
            CertificateVerificationEvent::create([
                'certificate_id' => $certificate?->id,
                'lookup_type' => $type,
                'lookup_hash' => $hash,
                'result_status' => $certificate ? $certificate->status->verificationLabel() : 'INVALID',
                'ip_prefix_hash' => request() ? hash('sha256', request()->ip().'|'.config('app.key')) : null,
                'user_agent_family' => request() ? substr((string) request()->userAgent(), 0, 60) : null,
            ]);
        } catch (\Throwable) {
            // Verification logging must never break the public lookup.
        }
    }

    public function assertRateLimited(): bool
    {
        $key = 'verify:'.(request()->ip() ?? 'unknown');

        return RateLimiter::tooManyAttempts($key, 30);
    }

    public function hitRateLimit(): void
    {
        RateLimiter::hit('verify:'.(request()->ip() ?? 'unknown'), 300);
    }
}
