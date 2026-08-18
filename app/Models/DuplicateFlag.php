<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class DuplicateFlag extends Model
{
    use UsesUuidV7;

    public const UPDATED_AT = null;

    protected $fillable = [
        'application_id', 'candidate_indigene_id', 'match_type', 'score', 'evidence', 'status',
        'reviewed_by', 'review_reason', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'evidence' => 'array',
            'score' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function application()
    {
        return $this->belongsTo(IndigeneApplication::class, 'application_id');
    }

    public function candidate()
    {
        return $this->belongsTo(Indigene::class, 'candidate_indigene_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function matchTypeLabel(): string
    {
        return str_replace('_', ' ', ucfirst($this->match_type));
    }
}
