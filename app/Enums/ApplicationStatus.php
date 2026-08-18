<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case PendingChairman = 'pending_chairman';
    case PendingSystemAdmin = 'pending_system_admin';
    case ChangesRequested = 'changes_requested';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted',
            self::PendingChairman => 'Awaiting Chairman',
            self::PendingSystemAdmin => 'Awaiting System Admin',
            self::ChangesRequested => 'Correction required',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function explanation(): string
    {
        return match ($this) {
            self::Draft => 'Saved but not submitted',
            self::Submitted => 'Submitted and awaiting routing',
            self::PendingChairman => 'Chairman or System Admin must decide',
            self::PendingSystemAdmin => 'System Admin must decide; used for Chairman-created applications',
            self::ChangesRequested => 'Return to the creator with stated corrections',
            self::Approved => 'Eligible for certificate issuance and printing',
            self::Rejected => 'Closed with a recorded reason; a new version may be submitted if permitted',
        };
    }

    public function isPendingDecision(): bool
    {
        return in_array($this, [self::PendingChairman, self::PendingSystemAdmin], true);
    }
}
