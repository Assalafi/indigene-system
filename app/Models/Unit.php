<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use UsesUuidV7;

    protected $fillable = [
        'lga_id', 'ward_id', 'district_id', 'parent_unit_id', 'code', 'name', 'category', 'status',
        'source_name', 'source_reference', 'import_batch_id', 'effective_from', 'effective_to',
        'merged_into_unit_id', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    public function lga()
    {
        return $this->belongsTo(Lga::class);
    }

    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isPollingUnit(): bool
    {
        return $this->category === 'polling_unit';
    }

    public function categoryLabel(): string
    {
        return str_replace('_', ' ', ucfirst($this->category));
    }
}
