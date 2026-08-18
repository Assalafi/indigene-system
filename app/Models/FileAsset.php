<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class FileAsset extends Model
{
    use UsesUuidV7;

    protected $fillable = [
        'storage_disk', 'object_key', 'original_name', 'mime_type', 'extension', 'size_bytes',
        'sha256', 'encryption_key_version', 'malware_scan_status', 'malware_scanned_at',
        'image_width', 'image_height', 'uploaded_by', 'status', 'retention_until',
    ];

    protected function casts(): array
    {
        return [
            'malware_scanned_at' => 'datetime',
            'retention_until' => 'date',
        ];
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
