<?php

namespace App\Models;

use App\Enums\LifecycleStatus;
use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class Indigene extends Model
{
    use UsesUuidV7;

    protected $fillable = [
        'registry_number', 'origin_state_id', 'origin_lga_id', 'current_profile_id',
        'nin_ciphertext', 'nin_hash', 'nin_last4', 'nin_verification_status', 'nin_verified_at',
        'nin_provider_reference', 'lifecycle_status', 'created_by', 'approved_at',
        'suspended_at', 'revoked_at',
    ];

    protected $hidden = ['nin_ciphertext'];

    protected function casts(): array
    {
        return [
            'nin_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'suspended_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function originState()
    {
        return $this->belongsTo(State::class, 'origin_state_id');
    }

    public function originLga()
    {
        return $this->belongsTo(Lga::class, 'origin_lga_id');
    }

    public function currentProfile()
    {
        return $this->belongsTo(IndigeneProfile::class, 'current_profile_id');
    }

    public function profiles()
    {
        return $this->hasMany(IndigeneProfile::class)->orderByDesc('version_no');
    }

    public function applications()
    {
        return $this->hasMany(IndigeneApplication::class)->orderByDesc('created_at');
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function maskedNin(): string
    {
        return $this->nin_last4 ? '*******'.$this->nin_last4 : '—';
    }

    public function fullName(): string
    {
        return optional($this->currentProfile)->fullName() ?? 'Unnamed';
    }

    public function isActive(): bool
    {
        return $this->lifecycle_status === LifecycleStatus::Active->value;
    }

    public function activeCertificate()
    {
        return $this->hasOne(Certificate::class)->whereIn('status', ['active', 'suspended'])->latestOfMany('issued_at');
    }
}
