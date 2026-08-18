<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class CertificateTemplate extends Model
{
    use UsesUuidV7;

    protected $fillable = [
        'name', 'code', 'scope_type', 'lga_id', 'version_no', 'blade_view', 'page_size',
        'orientation', 'configuration', 'preview_file_id', 'status', 'effective_from',
        'effective_to', 'created_by', 'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'configuration' => 'array',
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    public function lga()
    {
        return $this->belongsTo(Lga::class);
    }
}
