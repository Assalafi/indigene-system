<?php

namespace App\Enums;

enum CertificateStatus: string
{
    case Eligible = 'eligible';
    case Active = 'active';
    case Suspended = 'suspended';
    case Superseded = 'superseded';
    case Revoked = 'revoked';

    public function label(): string
    {
        return match ($this) {
            self::Eligible => 'Eligible',
            self::Active => 'Active',
            self::Suspended => 'Suspended',
            self::Superseded => 'Superseded',
            self::Revoked => 'Revoked',
        };
    }

    public function verificationLabel(): string
    {
        return match ($this) {
            self::Active, self::Eligible => 'VALID',
            self::Suspended => 'SUSPENDED',
            self::Superseded => 'SUPERSEDED',
            self::Revoked => 'REVOKED',
        };
    }
}
