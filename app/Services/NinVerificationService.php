<?php

namespace App\Services;

use App\Models\Indigene;

/**
 * SRD 45.2 - NINAuth provider adapter.
 *
 * In this baseline the provider integration is a stub: the system never
 * treats an uploaded NIN slip alone as verification and never approves
 * automatically because a provider call succeeded. When NIMC credentials
 * are onboarded, this adapter is replaced with the authorised API client
 * while keeping the same contract.
 */
class NinVerificationService
{
    public const PROVIDER_UNAVAILABLE = 'provider_unavailable';

    public function verify(Indigene $indigene, string $nin): array
    {
        // Keep the provider reference path explicit for future integration.
        return [
            'status' => 'pending',
            'message' => 'NINAuth provider integration is not yet configured for this deployment.',
            'provider_reference' => null,
        ];
    }

    public function isConfigured(): bool
    {
        return (bool) config('services.ninauth.enabled', false);
    }
}
