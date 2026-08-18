<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class UserLgaAssignment extends Model
{
    use UsesUuidV7;

    protected $fillable = [
        'user_id',
        'lga_id',
        'role_id',
        'assignment_type',
        'appointment_title',
        'appointment_reference',
        'starts_at',
        'ends_at',
        'is_primary',
        'status',
        'created_by',
        'ended_by',
        'end_reason',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_primary' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lga()
    {
        return $this->belongsTo(Lga::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
