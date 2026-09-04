<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class IndigeneApplication extends Model
{
    use UsesUuidV7;

    protected $fillable = [
        'application_number', 'indigene_id', 'profile_id', 'lga_id', 'application_type', 'status',
        'approval_route', 'created_by', 'submitted_by', 'submitted_at', 'assigned_reviewer_id',
        'decided_by', 'decided_at', 'decision_reason_code', 'decision_comment', 'priority', 'due_at',
        'last_saved_step', 'declaration_version', 'declaration_accepted_at', 'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'decided_at' => 'datetime',
            'due_at' => 'datetime',
            'declaration_accepted_at' => 'datetime',
            'status' => ApplicationStatus::class,
        ];
    }

    public function indigene()
    {
        return $this->belongsTo(Indigene::class);
    }

    public function profile()
    {
        return $this->belongsTo(IndigeneProfile::class);
    }

    public function lga()
    {
        return $this->belongsTo(Lga::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function assignedReviewer()
    {
        return $this->belongsTo(User::class, 'assigned_reviewer_id');
    }

    public function decider()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function statusHistories()
    {
        return $this->hasMany(ApplicationStatusHistory::class, 'application_id')->orderByDesc('created_at');
    }

    public function reviews()
    {
        return $this->hasMany(ApplicationReview::class, 'application_id')->orderByDesc('created_at');
    }

    public function duplicateFlags()
    {
        return $this->hasMany(DuplicateFlag::class, 'application_id');
    }

    public function openDuplicateFlags()
    {
        return $this->hasMany(DuplicateFlag::class, 'application_id')->where('status', 'open');
    }

    public function documents()
    {
        return $this->hasMany(ApplicationDocument::class, 'application_id');
    }

    public function consentRecords()
    {
        return $this->hasMany(ConsentRecord::class, 'application_id');
    }

    public function certificate()
    {
        return $this->hasOne(Certificate::class, 'approved_application_id');
    }

    public function statusLabel(): string
    {
        return $this->status->label();
    }

    public function queueAgeInDays(): ?float
    {
        if (! $this->submitted_at) {
            return null;
        }

        return now()->diffInDays($this->submitted_at);
    }

    public function routeTarget(): string
    {
        return match ($this->approval_route) {
            'admin_only' => 'System Admin',
            default => 'Chairman or System Admin',
        };
    }

    public function canBeSubmitted(): bool
    {
        // Any application can be edited and resubmitted — including rejected and
        // pending records. Approved records are handled separately (editing one
        // suspends its certificate and re-enters the approval queue).
        return $this->status !== ApplicationStatus::Approved;
    }

    public function canBeDecidedBy(User $user): bool
    {
        if ($user->isSystemAdmin()) {
            return $this->status->isPendingDecision();
        }

        if ($this->status !== ApplicationStatus::PendingChairman) {
            return false;
        }

        return $user->can('application.decide')
            && $this->lga_id === $user->activeLga()?->id;
    }
}
