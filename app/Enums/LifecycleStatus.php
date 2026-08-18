<?php

namespace App\Enums;

enum LifecycleStatus: string
{
    case Provisional = 'provisional';
    case Active = 'active';
    case Suspended = 'suspended';
    case Revoked = 'revoked';
    case Deceased = 'deceased';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
