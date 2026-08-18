<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    use UsesUuidV7;

    protected $fillable = [
        'lga_id', 'district_id', 'code', 'name', 'status', 'source_name', 'source_reference',
        'import_batch_id', 'effective_from', 'effective_to', 'merged_into_ward_id', 'created_by', 'updated_by',
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

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
