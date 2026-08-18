<?php

namespace App\Services;

use App\Models\Indigene;
use App\Models\IndigeneApplication;
use App\Models\IndigeneProfile;
use App\Models\DuplicateFlag;

/**
 * SRD 45.3 / FR-IND-003, FR-IND-010 - exact and fuzzy duplicate detection.
 * Fuzzy matching never makes the final indigene decision.
 */
class DuplicateDetectionService
{
    public function __construct(private NinProtectionService $nin) {}

    public function detect(Indigene $indigene, IndigeneProfile $profile, IndigeneApplication $application): void
    {
        $this->detectExactNin($indigene, $application);
        $this->detectFuzzyProfile($profile, $application);
        $this->detectPhoneAdvisory($profile, $application);
        $this->detectPhotoHashAdvisory($profile, $application);
    }

    public function exactNinCollision(Indigene $indigene, string $ninHash): ?Indigene
    {
        if (! $ninHash) {
            return null;
        }

        return Indigene::where('nin_hash', $ninHash)
            ->where('id', '!=', $indigene->id)
            ->first();
    }

    private function detectExactNin(Indigene $indigene, IndigeneApplication $application): void
    {
        if (! $indigene->nin_hash) {
            return;
        }

        $collision = $this->exactNinCollision($indigene, $indigene->nin_hash);

        if ($collision) {
            DuplicateFlag::firstOrCreate([
                'application_id' => $application->id,
                'match_type' => 'nin_exact',
                'candidate_indigene_id' => $collision->id,
            ], [
                'status' => 'open',
                'score' => 100.00,
                'evidence' => ['message' => 'Exact NIN hash match with another registry record.'],
            ]);
        }
    }

    private function detectFuzzyProfile(IndigeneProfile $profile, IndigeneApplication $application): void
    {
        $candidates = IndigeneProfile::query()
            ->where('id', '!=', $profile->id)
            ->where('surname', $profile->surname)
            ->where('first_name', $profile->first_name)
            ->whereDate('date_of_birth', $profile->date_of_birth)
            ->where('origin_lga_id', $profile->origin_lga_id)
            ->where('unit_id', $profile->unit_id)
            ->get();

        foreach ($candidates as $candidate) {
            DuplicateFlag::firstOrCreate([
                'application_id' => $application->id,
                'match_type' => 'profile_fuzzy',
                'candidate_indigene_id' => $candidate->indigene_id,
            ], [
                'status' => 'open',
                'score' => 85.00,
                'evidence' => [
                    'message' => 'Same name, date of birth, LGA and village/unit as another record.',
                ],
            ]);
        }
    }

    private function detectPhoneAdvisory(IndigeneProfile $profile, IndigeneApplication $application): void
    {
        if (! $profile->phone) {
            return;
        }

        $candidate = IndigeneProfile::where('id', '!=', $profile->id)
            ->where('phone', $profile->phone)
            ->where('indigene_id', '!=', $profile->indigene_id)
            ->first();

        if ($candidate) {
            DuplicateFlag::firstOrCreate([
                'application_id' => $application->id,
                'match_type' => 'phone',
                'candidate_indigene_id' => $candidate->indigene_id,
            ], [
                'status' => 'open',
                'score' => 25.00,
                'evidence' => ['message' => 'Phone number shared with another record (advisory only).'],
            ]);
        }
    }

    private function detectPhotoHashAdvisory(IndigeneProfile $profile, IndigeneApplication $application): void
    {
        $photo = $profile->photoFile;

        if (! $photo) {
            return;
        }

        $candidate = IndigeneProfile::whereHas('photoFile', function ($q) use ($photo) {
            $q->where('sha256', $photo->sha256);
        })
            ->where('id', '!=', $profile->id)
            ->where('indigene_id', '!=', $profile->indigene_id)
            ->first();

        if ($candidate) {
            DuplicateFlag::firstOrCreate([
                'application_id' => $application->id,
                'match_type' => 'document',
                'candidate_indigene_id' => $candidate->indigene_id,
            ], [
                'status' => 'open',
                'score' => 40.00,
                'evidence' => ['message' => 'Identical photograph file used by another record (advisory only).'],
            ]);
        }
    }
}
