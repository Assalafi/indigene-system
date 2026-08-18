<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class LegalHold extends Model
{
    use UsesUuidV7;

    protected $fillable = [
        'subject_type', 'subject_id', 'reason', 'authority_reference', 'starts_at', 'ends_at',
        'status', 'created_by', 'released_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }
}
