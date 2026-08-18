<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class OfficialSignatory extends Model
{
    use UsesUuidV7;

    protected $fillable = [
        'lga_id', 'full_name', 'office_title', 'signature_file_id', 'seal_file_id',
        'appointment_reference', 'effective_from', 'effective_to', 'status', 'is_primary',
        'created_by', 'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
            'is_primary' => 'boolean',
        ];
    }

    public function lga()
    {
        return $this->belongsTo(Lga::class);
    }

    public function signatureFile()
    {
        return $this->belongsTo(FileAsset::class, 'signature_file_id');
    }

    public function sealFile()
    {
        return $this->belongsTo(FileAsset::class, 'seal_file_id');
    }
}
