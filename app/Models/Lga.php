<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class Lga extends Model
{
    use UsesUuidV7;

    protected $fillable = [
        'state_id', 'code', 'name', 'type', 'headquarters', 'status',
        'source_name', 'source_reference', 'effective_from', 'effective_to',
        'merged_into_lga_id', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function districts()
    {
        return $this->hasMany(District::class);
    }

    public function wards()
    {
        return $this->hasMany(Ward::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function profile()
    {
        return $this->hasOne(LgaProfile::class)->where('status', 'published')->latestOfMany('version_no');
    }

    public function profiles()
    {
        return $this->hasMany(LgaProfile::class)->orderByDesc('version_no');
    }

    public function signatories()
    {
        return $this->hasMany(OfficialSignatory::class)->orderByDesc('effective_from');
    }

    public function activeSignatory()
    {
        return $this->hasOne(OfficialSignatory::class)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', now()->toDateString());
            })
            ->latestOfMany('effective_from');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function fullLabel(): string
    {
        return "{$this->name}, {$this->state->name}";
    }
}
