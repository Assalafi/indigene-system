<?php

namespace App\Models;

use App\Enums\UserStatus;
use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, UsesUuidV7, HasRoles;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'password',
        'status',
        'email_verified_at',
        'phone_verified_at',
        'must_change_password',
        'last_login_at',
        'last_login_ip_hash',
        'created_by',
        'suspended_by',
        'suspended_at',
        'suspension_reason',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'suspended_at' => 'datetime',
            'must_change_password' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function assignments()
    {
        return $this->hasMany(UserLgaAssignment::class);
    }

    public function activeAssignments()
    {
        return $this->hasMany(UserLgaAssignment::class)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function activeLga(): ?Lga
    {
        $assignment = $this->activeAssignments()->where('is_primary', true)->first()
            ?? $this->activeAssignments()->first();

        return $assignment?->lga;
    }

    public function activeAssignment(): ?UserLgaAssignment
    {
        return $this->activeAssignments()->where('is_primary', true)->first()
            ?? $this->activeAssignments()->first();
    }

    public function primaryRoleName(): string
    {
        return optional($this->roles()->first())->name ?? 'Staff';
    }

    public function isSystemAdmin(): bool
    {
        return $this->hasRole('system_admin');
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active->value;
    }

    public function scopeActive($query)
    {
        return $query->where('status', UserStatus::Active->value);
    }
}
