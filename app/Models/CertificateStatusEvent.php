<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class CertificateStatusEvent extends Model
{
    use UsesUuidV7;

    public const UPDATED_AT = null;

    protected $fillable = [
        'certificate_id', 'from_status', 'to_status', 'reason_code', 'reason_text',
        'evidence_file_id', 'effective_at', 'actor_id', 'actor_role', 'actor_lga_id',
    ];

    protected function casts(): array
    {
        return [
            'effective_at' => 'datetime',
        ];
    }

    public function certificate()
    {
        return $this->belongsTo(Certificate::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
