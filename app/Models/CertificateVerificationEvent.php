<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class CertificateVerificationEvent extends Model
{
    use UsesUuidV7;

    public const UPDATED_AT = null;

    protected $fillable = [
        'certificate_id', 'lookup_type', 'lookup_hash', 'result_status', 'ip_prefix_hash',
        'user_agent_family', 'country_code', 'risk_score',
    ];

    public function certificate()
    {
        return $this->belongsTo(Certificate::class);
    }
}
