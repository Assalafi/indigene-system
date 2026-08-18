<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class ReportExport extends Model
{
    use UsesUuidV7;

    public const UPDATED_AT = null;

    protected $fillable = [
        'report_code', 'requested_by', 'lga_scope_id', 'filters', 'format', 'purpose', 'status',
        'file_id', 'expires_at', 'row_count', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'expires_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function lgaScope()
    {
        return $this->belongsTo(Lga::class, 'lga_scope_id');
    }

    public function file()
    {
        return $this->belongsTo(FileAsset::class, 'file_id');
    }
}
