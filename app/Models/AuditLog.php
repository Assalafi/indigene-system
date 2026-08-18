<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use UsesUuidV7;

    public const UPDATED_AT = null;

    public const CREATED_AT = 'occurred_at';

    protected $fillable = [
        'actor_id', 'actor_type', 'actor_role', 'actor_lga_id', 'action', 'auditable_type',
        'auditable_id', 'request_id', 'route_name', 'http_method', 'result', 'risk_level',
        'before_values', 'after_values', 'ip_hash', 'user_agent', 'occurred_at',
        'previous_hash', 'event_hash',
    ];

    protected function casts(): array
    {
        return [
            'before_values' => 'array',
            'after_values' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function actorLga()
    {
        return $this->belongsTo(Lga::class, 'actor_lga_id');
    }
}
