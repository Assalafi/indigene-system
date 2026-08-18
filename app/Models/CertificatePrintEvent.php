<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class CertificatePrintEvent extends Model
{
    use UsesUuidV7;

    public const UPDATED_AT = null;

    protected $fillable = [
        'certificate_id', 'certificate_version_id', 'print_number', 'copy_type', 'reason_code',
        'reason_note', 'requested_by', 'requester_role', 'requester_lga_id', 'idempotency_key_hash',
        'pdf_file_id', 'ip_hash', 'user_agent',
    ];

    public function certificate()
    {
        return $this->belongsTo(Certificate::class);
    }

    public function version()
    {
        return $this->belongsTo(CertificateVersion::class, 'certificate_version_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function pdfFile()
    {
        return $this->belongsTo(FileAsset::class, 'pdf_file_id');
    }

    public function copyLabel(): string
    {
        return $this->copy_type === 'original'
            ? "ORIGINAL - COPY 01"
            : "REPRINT - COPY ".str_pad((string) $this->print_number, 2, '0', STR_PAD_LEFT);
    }
}
