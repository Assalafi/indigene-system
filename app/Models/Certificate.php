<?php

namespace App\Models;

use App\Enums\CertificateStatus;
use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use UsesUuidV7;

    protected $fillable = [
        'indigene_id', 'approved_application_id', 'lga_id', 'certificate_number', 'status',
        'current_version_id', 'public_token_hash', 'public_token_hint', 'issued_at', 'expires_at',
        'approved_by', 'total_prints_cached', 'superseded_by_certificate_id',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'expires_at' => 'datetime',
            'status' => CertificateStatus::class,
        ];
    }

    public function indigene()
    {
        return $this->belongsTo(Indigene::class);
    }

    public function application()
    {
        return $this->belongsTo(IndigeneApplication::class, 'approved_application_id');
    }

    public function lga()
    {
        return $this->belongsTo(Lga::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function versions()
    {
        return $this->hasMany(CertificateVersion::class)->orderByDesc('version_no');
    }

    public function currentVersion()
    {
        return $this->belongsTo(CertificateVersion::class, 'current_version_id');
    }

    public function printEvents()
    {
        return $this->hasMany(CertificatePrintEvent::class)->orderByDesc('created_at');
    }

    public function statusEvents()
    {
        return $this->hasMany(CertificateStatusEvent::class)->orderByDesc('created_at');
    }

    public function statusLabel(): string
    {
        return $this->status->label();
    }

    public function isPrintable(): bool
    {
        return $this->status === CertificateStatus::Active;
    }
}
