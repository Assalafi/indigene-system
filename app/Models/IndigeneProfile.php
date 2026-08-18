<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class IndigeneProfile extends Model
{
    use UsesUuidV7;

    protected $fillable = [
        'indigene_id', 'version_no', 'title', 'surname', 'first_name', 'middle_name', 'other_names',
        'sex', 'date_of_birth', 'marital_status', 'nationality', 'occupation', 'phone', 'email',
        'origin_state_id', 'origin_lga_id', 'district_id', 'ward_id', 'unit_id', 'hometown',
        'residential_address', 'residence_state_id', 'residence_lga_id', 'residence_town',
        'indigene_basis', 'photo_file_id', 'profile_status', 'is_current', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_current' => 'boolean',
        ];
    }

    public function indigene()
    {
        return $this->belongsTo(Indigene::class);
    }

    public function originState()
    {
        return $this->belongsTo(State::class, 'origin_state_id');
    }

    public function originLga()
    {
        return $this->belongsTo(Lga::class, 'origin_lga_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function residenceState()
    {
        return $this->belongsTo(State::class, 'residence_state_id');
    }

    public function residenceLga()
    {
        return $this->belongsTo(Lga::class, 'residence_lga_id');
    }

    public function photoFile()
    {
        return $this->belongsTo(FileAsset::class, 'photo_file_id');
    }

    public function relations()
    {
        return $this->hasMany(IndigeneRelation::class, 'profile_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fullName(): string
    {
        return trim(implode(' ', array_filter([
            $this->title ? "{$this->title}." : null,
            $this->surname,
            $this->first_name,
            $this->middle_name,
            $this->other_names,
        ])));
    }

    public function displayName(): string
    {
        return trim(implode(' ', array_filter([
            $this->surname,
            $this->first_name,
            $this->middle_name,
        ])));
    }

    public function age(): ?int
    {
        return $this->date_of_birth?->age;
    }

    public function isMinor(): bool
    {
        return ($this->age() ?? 0) < 18;
    }

    public function guardian()
    {
        return $this->hasOne(IndigeneRelation::class, 'profile_id')->where('relation_type', 'guardian');
    }

    public function nextOfKin()
    {
        return $this->hasOne(IndigeneRelation::class, 'profile_id')->where('relation_type', 'next_of_kin');
    }

    public function father()
    {
        return $this->hasOne(IndigeneRelation::class, 'profile_id')->where('relation_type', 'father');
    }

    public function mother()
    {
        return $this->hasOne(IndigeneRelation::class, 'profile_id')->where('relation_type', 'mother');
    }
}
