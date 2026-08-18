<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class SensitiveDataAccessLog extends Model
{
    use UsesUuidV7;

    public const UPDATED_AT = null;

    protected $fillable = [
        'actor_id', 'subject_type', 'subject_id', 'data_category', 'action', 'purpose',
        'approval_reference', 'result', 'ip_hash',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
