<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class CertificateVersion extends Model
{
    use UsesUuidV7;

    public const UPDATED_AT = null;

    protected $fillable = [
        'certificate_id', 'version_no', 'certificate_template_id', 'lga_profile_id', 'signatory_id',
        'source_profile_id', 'snapshot_ciphertext', 'pdf_file_id', 'pdf_sha256', 'qr_payload_hash',
        'generated_by', 'generated_at', 'status',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
        ];
    }

    public function certificate()
    {
        return $this->belongsTo(Certificate::class);
    }

    public function template()
    {
        return $this->belongsTo(CertificateTemplate::class, 'certificate_template_id');
    }

    public function lgaProfile()
    {
        return $this->belongsTo(LgaProfile::class);
    }

    public function signatory()
    {
        return $this->belongsTo(OfficialSignatory::class);
    }

    public function sourceProfile()
    {
        return $this->belongsTo(IndigeneProfile::class, 'source_profile_id');
    }

    public function pdfFile()
    {
        return $this->belongsTo(FileAsset::class, 'pdf_file_id');
    }
}
