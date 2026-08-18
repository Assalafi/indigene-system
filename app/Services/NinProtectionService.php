<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * SRD 45 - NIN protection: normalisation, encryption, keyed HMAC, masking and reveal.
 *
 * Three representations:
 *  - encrypted ciphertext (envelope encryption via app key)
 *  - keyed HMAC for exact duplicate lookup
 *  - last four digits for masked display
 */
class NinProtectionService
{
    public function normalize(?string $nin): ?string
    {
        if ($nin === null) {
            return null;
        }

        $nin = preg_replace('/\D/', '', $nin);

        return $nin === '' ? null : $nin;
    }

    public function validate(?string $nin): bool
    {
        $nin = $this->normalize($nin);

        return $nin !== null && strlen($nin) === 11 && ctype_digit($nin);
    }

    public function encrypt(?string $nin): ?string
    {
        $nin = $this->normalize($nin);

        return $nin === null ? null : Crypt::encryptString($nin);
    }

    public function decrypt(?string $ciphertext): ?string
    {
        if ($ciphertext === null) {
            return null;
        }

        try {
            return Crypt::decryptString($ciphertext);
        } catch (\Throwable) {
            return null;
        }
    }

    public function hash(?string $nin): ?string
    {
        $nin = $this->normalize($nin);

        if ($nin === null) {
            return null;
        }

        return hash_hmac('sha256', $nin, config('services.nin.hmac_key', config('app.key')));
    }

    public function last4(?string $nin): ?string
    {
        $nin = $this->normalize($nin);

        return $nin === null ? null : substr($nin, -4);
    }

    public function mask(?string $nin): string
    {
        $last4 = $this->last4($nin);

        return $last4 ? '*******'.$last4 : '—';
    }

    public function reveal(?string $ciphertext): ?string
    {
        return $this->decrypt($ciphertext);
    }
}
