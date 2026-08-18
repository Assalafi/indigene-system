<?php

namespace App\Services;

use App\Models\District;
use App\Models\Lga;
use App\Models\State;
use App\Models\Unit;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Validation\ValidationException;

/**
 * SRD 43 - geography validation rules and user LGA scoping.
 */
class GeographyScopeService
{
    public function validateHierarchy(State $state, Lga $lga, ?District $district, Ward $ward, Unit $unit): void
    {
        if ($lga->state_id !== $state->id) {
            throw ValidationException::withMessages([
                'lga_id' => 'The selected LGA does not belong to the selected state.',
            ]);
        }

        if ($district && $district->lga_id !== $lga->id) {
            throw ValidationException::withMessages([
                'district_id' => 'The selected district does not belong to the selected LGA.',
            ]);
        }

        if ($ward->lga_id !== $lga->id) {
            throw ValidationException::withMessages([
                'ward_id' => 'The selected ward does not belong to the selected LGA.',
            ]);
        }

        if ($district && $ward->district_id && $ward->district_id !== $district->id) {
            throw ValidationException::withMessages([
                'ward_id' => 'The selected ward does not belong to the selected district.',
            ]);
        }

        if ($unit->ward_id !== $ward->id || $unit->lga_id !== $lga->id) {
            throw ValidationException::withMessages([
                'unit_id' => 'The selected village/community unit does not belong to the selected ward and LGA.',
            ]);
        }
    }

    /**
     * The browser-provided lga_id is never trusted; scope derives from active assignment.
     */
    public function authorisedLga(User $user): ?Lga
    {
        if ($user->isSystemAdmin()) {
            return null; // national scope
        }

        return $user->activeLga();
    }

    public function assertInScope(User $user, ?Lga $lga): void
    {
        if ($user->isSystemAdmin()) {
            return;
        }

        $assigned = $user->activeLga();

        if (! $assigned || ! $lga || $assigned->id !== $lga->id) {
            abort(404);
        }
    }

    public function assertCanSelectLga(User $user, Lga $lga): void
    {
        if ($user->isSystemAdmin()) {
            return;
        }

        $assigned = $user->activeLga();

        if (! $assigned || $assigned->id !== $lga->id) {
            abort(403, 'You can only operate within your assigned LGA.');
        }
    }

    public function assertActive(?State $state = null, ?Lga $lga = null, ?District $district = null, ?Ward $ward = null, ?Unit $unit = null): void
    {
        foreach (compact('state', 'lga', 'district', 'ward', 'unit') as $name => $record) {
            if ($record && $record->status !== 'active') {
                throw ValidationException::withMessages([
                    $name.'_id' => "The selected {$name} is no longer active.",
                ]);
            }
        }
    }
}
