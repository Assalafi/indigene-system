<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class ApplicationStatusHistory extends Model
{
    use UsesUuidV7;

    public const UPDATED_AT = null;

    protected $fillable = [
        'application_id', 'from_status', 'to_status', 'action', 'actor_id', 'actor_role',
        'actor_lga_id', 'public_comment', 'internal_comment', 'reason_code', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function application()
    {
        return $this->belongsTo(IndigeneApplication::class, 'application_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
