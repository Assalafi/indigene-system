<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class ApplicationReview extends Model
{
    use UsesUuidV7;

    public const UPDATED_AT = null;

    protected $fillable = [
        'application_id', 'reviewer_id', 'review_type', 'outcome', 'checklist_version',
        'checklist', 'risk_flags', 'public_comment', 'internal_comment', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'checklist' => 'array',
            'risk_flags' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function application()
    {
        return $this->belongsTo(IndigeneApplication::class, 'application_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
