<?php

namespace App\Services;

use App\Models\Indigene;
use App\Models\IndigeneProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * SRD 29.1 - draft/current profile versions and material-change detection.
 */
class IndigeneProfileVersionService
{
    public function registryNumber(Indigene $indigene): string
    {
        if ($indigene->registry_number && ! str_starts_with($indigene->registry_number, 'TMP-')) {
            return $indigene->registry_number;
        }

        $year = now()->format('Y');
        $prefix = 'REG-'.$year.'-';

        return DB::transaction(function () use ($indigene, $prefix, $year) {
            $max = Indigene::where('registry_number', 'like', $prefix.'%')
                ->lockForUpdate()
                ->max('registry_number');

            $next = $max ? ((int) substr($max, -6)) + 1 : 1;

            $number = $prefix.str_pad((string) $next, 6, '0', STR_PAD_LEFT);

            $indigene->registry_number = $number;
            $indigene->save();

            return $number;
        });
    }

    public function copyCurrentProfileForAmendment(Indigene $indigene): IndigeneProfile
    {
        $current = $indigene->currentProfile;

        if (! $current) {
            abort(404, 'No approved profile to amend.');
        }

        $nextVersion = IndigeneProfile::where('indigene_id', $indigene->id)->max('version_no') + 1;

        return IndigeneProfile::create([
            'indigene_id' => $indigene->id,
            'version_no' => $nextVersion,
            'title' => $current->title,
            'surname' => $current->surname,
            'first_name' => $current->first_name,
            'middle_name' => $current->middle_name,
            'other_names' => $current->other_names,
            'sex' => $current->sex,
            'date_of_birth' => $current->date_of_birth,
            'marital_status' => $current->marital_status,
            'nationality' => $current->nationality,
            'occupation' => $current->occupation,
            'phone' => $current->phone,
            'email' => $current->email,
            'origin_state_id' => $current->origin_state_id,
            'origin_lga_id' => $current->origin_lga_id,
            'district_id' => $current->district_id,
            'ward_id' => $current->ward_id,
            'unit_id' => $current->unit_id,
            'hometown' => $current->hometown,
            'residential_address' => $current->residential_address,
            'residence_state_id' => $current->residence_state_id,
            'residence_lga_id' => $current->residence_lga_id,
            'residence_town' => $current->residence_town,
            'indigene_basis' => $current->indigene_basis,
            'photo_file_id' => $current->photo_file_id,
            'profile_status' => 'draft',
            'is_current' => false,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * SRD 14.1 rule 10: any change to name, date of birth, NIN, LGA/ward/unit of origin or
     * photograph after approval requires an amendment application and reapproval.
     */
    public function isMaterialChange(IndigeneProfile $current, IndigeneProfile $draft, ?string $ninChanged = null): bool
    {
        return $current->surname !== $draft->surname
            || $current->first_name !== $draft->first_name
            || $current->middle_name !== $draft->middle_name
            || $current->date_of_birth?->toDateString() !== $draft->date_of_birth?->toDateString()
            || $current->origin_lga_id !== $draft->origin_lga_id
            || $current->ward_id !== $draft->ward_id
            || $current->unit_id !== $draft->unit_id
            || $current->photo_file_id !== $draft->photo_file_id
            || $ninChanged === true;
    }

    public function applicationNumber(): string
    {
        $year = now()->format('Y');
        $prefix = 'APP-'.$year.'-';

        $max = \App\Models\IndigeneApplication::where('application_number', 'like', $prefix.'%')->max('application_number');
        $next = $max ? ((int) substr($max, -6)) + 1 : 1;

        return $prefix.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    public function fraudReference(): string
    {
        return 'FR-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
    }

    public function privacyReference(): string
    {
        return 'PR-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
    }
}
