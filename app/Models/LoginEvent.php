<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class LoginEvent extends Model
{
    use UsesUuidV7;

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'identity_hash', 'event_type', 'success', 'ip_hash', 'user_agent', 'risk_flags',
    ];

    protected function casts(): array
    {
        return [
            'success' => 'boolean',
            'risk_flags' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
