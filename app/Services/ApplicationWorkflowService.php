<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\CertificateStatus;
use App\Enums\LifecycleStatus;
use App\Models\ApplicationReview;
use App\Models\ApplicationStatusHistory;
use App\Models\Certificate;
use App\Models\Indigene;
use App\Models\IndigeneApplication;
use App\Models\IndigeneProfile;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * SRD 14 - application workflow: legal status transitions, routing and separation of duties.
 */
class ApplicationWorkflowService
{
    public function __construct(private AuditService $audit) {}

    public function submit(IndigeneApplication $application, User $user): void
    {
        // Editing an approved record updates it in place: no status change, no
        // re-approval, and the certificate is re-issued (new version, same number)
        // with the corrected data so realtime prints reflect the change.
        if ($application->status === ApplicationStatus::Approved) {
            $this->applyApprovedEdit($application, $user);

            return;
        }

        if ($user->id === $application->created_by && ! $user->isSystemAdmin()) {
            throw new HttpException(403, 'This application can only be edited and resubmitted by its creator or a System Admin.');
        }

        // SRD 14.1 rule 4: exact and fuzzy duplicate checks run before submission.
        app(DuplicateDetectionService::class)->detect(
            $application->indigene,
            $application->profile,
            $application
        );

        $creatorIsChairman = $user->hasRole('lga_chairman');

        $route = $creatorIsChairman ? 'admin_only' : 'chairman_or_admin';
        $target = $creatorIsChairman
            ? ApplicationStatus::PendingSystemAdmin
            : ApplicationStatus::PendingChairman;

        $from = $application->status->value;

        DB::transaction(function () use ($application, $user, $route, $target, $from) {
            $application->approval_route = $route;
            $application->status = $target;
            $application->submitted_by = $user->id;
            $application->submitted_at = now();
            $application->due_at = now()->addDays((int) SystemSetting::getSetting('application_due_days', 7));
            $application->decided_by = null;
            $application->decided_at = null;
            $application->decision_reason_code = null;
            $application->decision_comment = null;
            $application->save();

            $application->profile->update(['profile_status' => 'submitted']);

            $this->history($application, $from, $target->value, 'submit', $user,
                'Application submitted for review.');
        });

        $this->audit->record('application.submitted', IndigeneApplication::class, $application->id, [
            'from' => $from,
        ], [
            'status' => $target->value,
            'route' => $route,
        ], 'medium', $user);

        $this->notifyQueue($application, $user);
    }

    private function applyApprovedEdit(IndigeneApplication $application, User $user): void
    {
        $renderer = app(CertificateRenderService::class);

        $newVersion = DB::transaction(function () use ($application, $user, $renderer) {
            $issuedVersion = null;

            // Re-issue the certificate(s) for this record in place — same number,
            // same status (active), corrected snapshot.
            foreach ($application->indigene->certificates()
                ->whereIn('status', [
                    CertificateStatus::Active->value,
                    CertificateStatus::Eligible->value,
                ])
                ->get() as $certificate) {
                $issuedVersion = $renderer->refreshForEdit($certificate, $user);
            }

            // Application stays approved; only the profile/version has moved forward.
            $application->profile->update(['profile_status' => 'current']);

            $this->history($application, ApplicationStatus::Approved->value, ApplicationStatus::Approved->value,
                'edit_approved', $user,
                'Record edited in place. Certificate re-issued (version '.($issuedVersion?->version_no ?? '—').', same number).');

            return $issuedVersion;
        });

        $this->audit->record('application.edited_approved', IndigeneApplication::class, $application->id, [], [
            'certificate_version' => $newVersion?->version_no,
        ], 'high', $user);
    }

    /**
     * SRD 65 - notify the review queue. Notification text contains no NIN or private detail.
     */
    private function notifyQueue(IndigeneApplication $application, User $submitter): void
    {
        $lgaName = $application->lga->name;
        $message = "New application {$application->application_number} for {$application->indigene->fullName()} in {$lgaName} LGA awaits a decision.";

        $recipients = \App\Models\User::where('status', 'active')
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['system_admin', 'lga_chairman']))
            ->where(function ($q) use ($application) {
                $q->whereHas('roles', fn ($r) => $r->where('name', 'system_admin'))
                    ->orWhereHas('assignments', fn ($a) => $a
                        ->where('lga_id', $application->lga_id)
                        ->where('status', 'active'));
            })
            ->where('id', '!=', $submitter->id)
            ->get();

        foreach ($recipients as $recipient) {
            try {
                $recipient->notify(new \App\Notifications\ApplicationSubmittedNotification($application, $message));
            } catch (\Throwable) {
                // Delivery failure never invalidates the submission.
            }
        }
    }

    public function approve(IndigeneApplication $application, User $user, array $checklist = [], ?string $publicComment = null, ?string $internalComment = null, bool $isOverride = false): void
    {
        if ($user->id === $application->created_by && ! $user->isSystemAdmin()) {
            throw new HttpException(403, 'You cannot approve an application you created.');
        }

        if ($application->created_by === $user->id && $user->isSystemAdmin() && ! $isOverride) {
            throw new HttpException(403, 'Approving your own application requires an authorised override.');
        }

        if (! $application->status->isPendingDecision()) {
            throw new HttpException(409, 'This application is not awaiting a decision.');
        }

        $this->resolveOpenDuplicateBlocks($application);

        DB::transaction(function () use ($application, $user, $checklist, $publicComment, $internalComment, $isOverride) {
            $from = $application->status->value;

            $application->status = ApplicationStatus::Approved;
            $application->decided_by = $user->id;
            $application->decided_at = now();
            $application->decision_comment = $publicComment;
            $application->save();

            $profile = $application->profile;
            $profile->profile_status = 'current';
            $profile->is_current = true;
            $profile->save();

            IndigeneProfile::where('indigene_id', $application->indigene_id)
                ->where('id', '!=', $profile->id)
                ->where('is_current', true)
                ->update(['is_current' => false, 'profile_status' => 'superseded']);

            $indigene = $application->indigene;
            $indigene->current_profile_id = $profile->id;
            $indigene->lifecycle_status = LifecycleStatus::Active->value;
            $indigene->approved_at = now();
            $indigene->save();

            ApplicationReview::create([
                'application_id' => $application->id,
                'reviewer_id' => $user->id,
                'review_type' => 'approval',
                'outcome' => 'approved',
                'checklist_version' => '1.0',
                'checklist' => $checklist,
                'risk_flags' => $isOverride ? ['override' => true] : null,
                'public_comment' => $publicComment,
                'internal_comment' => $internalComment,
                'reviewed_at' => now(),
            ]);

            $this->history($application, $from, ApplicationStatus::Approved->value, 'approve', $user,
                $publicComment, $internalComment, $isOverride ? 'admin_override' : null);

            // Certificate eligibility is activated in the same transaction (SRD 14.1 rule 7).
            $this->activateCertificateEligibility($application, $user);
        });

        $this->audit->record($isOverride ? 'application.approved_override' : 'application.approved', IndigeneApplication::class, $application->id, [
            'from' => $from ?? null,
        ], [
            'to' => ApplicationStatus::Approved->value,
        ], $isOverride ? 'high' : 'medium', $user);

        $this->notifyCreator($application, 'approved', "Application {$application->application_number} was approved. The record is now eligible for certificate issuance.");

        if ($isOverride) {
            $this->audit->recordSensitiveAccess(
                IndigeneApplication::class,
                $application->id,
                'approval_override',
                'approve',
                'System Admin override approval'
            );
        }
    }

    public function reject(IndigeneApplication $application, User $user, string $reasonCode, string $publicComment, ?string $internalComment = null): void
    {
        if (! $application->status->isPendingDecision()) {
            throw new HttpException(409, 'This application is not awaiting a decision.');
        }

        DB::transaction(function () use ($application, $user, $reasonCode, $publicComment, $internalComment) {
            $from = $application->status->value;

            $application->status = ApplicationStatus::Rejected;
            $application->decided_by = $user->id;
            $application->decided_at = now();
            $application->decision_reason_code = $reasonCode;
            $application->decision_comment = $publicComment;
            $application->save();

            $application->profile->update(['profile_status' => 'rejected']);

            ApplicationReview::create([
                'application_id' => $application->id,
                'reviewer_id' => $user->id,
                'review_type' => 'rejection',
                'outcome' => 'rejected',
                'checklist_version' => '1.0',
                'public_comment' => $publicComment,
                'internal_comment' => $internalComment,
                'reviewed_at' => now(),
            ]);

            $this->history($application, $from, ApplicationStatus::Rejected->value, 'reject', $user,
                $publicComment, $internalComment, $reasonCode);
        });

        $this->audit->record('application.rejected', IndigeneApplication::class, $application->id, [
            'from' => $from ?? null,
            'reason_code' => $reasonCode,
        ], [
            'to' => ApplicationStatus::Rejected->value,
        ], 'medium', $user);

        $this->notifyCreator($application, 'rejected', "Application {$application->application_number} was rejected. Reason: {$reasonCode}.");
    }

    public function requestCorrection(IndigeneApplication $application, User $user, array $corrections, string $publicComment, ?string $internalComment = null): void
    {
        if (! $application->status->isPendingDecision()) {
            throw new HttpException(409, 'This application is not awaiting a decision.');
        }

        DB::transaction(function () use ($application, $user, $corrections, $publicComment, $internalComment) {
            $from = $application->status->value;

            $application->status = ApplicationStatus::ChangesRequested;
            $application->decided_by = $user->id;
            $application->decided_at = now();
            $application->decision_comment = $publicComment;
            $application->save();

            $application->profile->update(['profile_status' => 'draft']);

            ApplicationReview::create([
                'application_id' => $application->id,
                'reviewer_id' => $user->id,
                'review_type' => 'correction',
                'outcome' => 'changes_requested',
                'checklist_version' => '1.0',
                'checklist' => $corrections,
                'public_comment' => $publicComment,
                'internal_comment' => $internalComment,
                'reviewed_at' => now(),
            ]);

            $this->history($application, $from, ApplicationStatus::ChangesRequested->value, 'request_correction',
                $user, $publicComment, $internalComment, null, ['corrections' => $corrections]);
        });

        $this->audit->record('application.correction_requested', IndigeneApplication::class, $application->id, [
            'from' => $from ?? null,
        ], [
            'to' => ApplicationStatus::ChangesRequested->value,
        ], 'medium', $user);

        $this->notifyCreator($application, 'correction', "Application {$application->application_number} requires corrections. Review the checklist and resubmit.");
    }

    private function notifyCreator(IndigeneApplication $application, string $type, string $message): void
    {
        $creator = $application->creator;

        if (! $creator || ! $creator->isActive()) {
            return;
        }

        try {
            $creator->notify(new \App\Notifications\ApplicationDecisionNotification($application, $type, $message));
        } catch (\Throwable) {
            // Notification failure never invalidates the decision.
        }
    }

    private function activateCertificateEligibility(IndigeneApplication $application, User $user): void
    {
        $existing = Certificate::where('approved_application_id', $application->id)->get();

        if ($existing->isNotEmpty()) {
            // Re-approval after an edit: return the existing certificate to Eligible
            // so it is re-issued (new version, same number) with the corrected snapshot.
            $returnedToEligible = false;

            foreach ($existing as $cert) {
                if (! in_array($cert->status, [
                    CertificateStatus::Revoked->value,
                    CertificateStatus::Superseded->value,
                ], true)) {
                    $cert->status = CertificateStatus::Eligible->value;
                    $cert->save();
                    $returnedToEligible = true;
                }
            }

            if ($returnedToEligible) {
                return;
            }
        }

        Certificate::create([
            'indigene_id' => $application->indigene_id,
            'approved_application_id' => $application->id,
            'lga_id' => $application->lga_id,
            'certificate_number' => null,
            'status' => CertificateStatus::Eligible,
            'public_token_hash' => hash('sha256', random_bytes(32)),
            'issued_at' => null,
            'approved_by' => $user->id,
        ]);
    }

    private function resolveOpenDuplicateBlocks(IndigeneApplication $application): void
    {
        foreach ($application->duplicateFlags()->where('status', 'open')->get() as $flag) {
            if ($flag->match_type === 'nin_exact') {
                throw new HttpException(409, 'An exact NIN duplicate flag must be resolved before approval.');
            }
        }
    }

    private function history(IndigeneApplication $application, string $from, string $to, string $action, User $user, ?string $public = null, ?string $internal = null, ?string $reasonCode = null, array $metadata = []): void
    {
        ApplicationStatusHistory::create([
            'application_id' => $application->id,
            'from_status' => $from,
            'to_status' => $to,
            'action' => $action,
            'actor_id' => $user->id,
            'actor_role' => $user->roles()->first()?->name,
            'actor_lga_id' => $user->activeLga()?->id,
            'public_comment' => $public,
            'internal_comment' => $internal,
            'reason_code' => $reasonCode,
            'metadata' => $metadata ?: null,
        ]);
    }
}
