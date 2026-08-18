<?php

namespace App\Enums;

enum UserStatus: string
{
    case Invited = 'invited';
    case Active = 'active';
    case Suspended = 'suspended';
    case Locked = 'locked';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
