<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class GeographyImportBatch extends Model
{
    use UsesUuidV7;

    protected $fillable = [
        'source_name', 'source_reference', 'dataset_type', 'dataset_version', 'source_date',
        'file_asset_id', 'checksum_sha256', 'status', 'row_count', 'inserted_count', 'updated_count',
        'skipped_count', 'error_count', 'validation_report', 'imported_by', 'published_by', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'source_date' => 'date',
            'published_at' => 'datetime',
            'validation_report' => 'array',
        ];
    }

    public function fileAsset()
    {
        return $this->belongsTo(FileAsset::class);
    }

    public function importer()
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function publisher()
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
