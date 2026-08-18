<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class ApplicationDocument extends Model
{
    use UsesUuidV7;

    protected $fillable = [
        'application_id', 'profile_id', 'file_asset_id', 'document_type',
        'document_number_ciphertext', 'issuing_authority', 'issued_at', 'expires_at',
        'verification_status', 'verified_by', 'verified_at', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'expires_at' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    public function application()
    {
        return $this->belongsTo(IndigeneApplication::class, 'application_id');
    }

    public function profile()
    {
        return $this->belongsTo(IndigeneProfile::class, 'profile_id');
    }

    public function fileAsset()
    {
        return $this->belongsTo(FileAsset::class);
    }

    public function documentTypeLabel(): string
    {
        return str_replace('_', ' ', ucfirst($this->document_type));
    }
}
