<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class ConsentRecord extends Model
{
    use UsesUuidV7;

    public const UPDATED_AT = null;

    protected $fillable = [
        'indigene_id', 'application_id', 'data_subject_type', 'relation_id', 'notice_version',
        'lawful_basis', 'purpose_codes', 'consent_required', 'accepted', 'captured_method',
        'captured_by', 'evidence_file_id', 'ip_hash', 'user_agent', 'captured_at', 'withdrawn_at',
    ];

    protected function casts(): array
    {
        return [
            'purpose_codes' => 'array',
            'consent_required' => 'boolean',
            'accepted' => 'boolean',
            'captured_at' => 'datetime',
            'withdrawn_at' => 'datetime',
        ];
    }

    public function indigene()
    {
        return $this->belongsTo(Indigene::class);
    }

    public function application()
    {
        return $this->belongsTo(IndigeneApplication::class, 'application_id');
    }
}
