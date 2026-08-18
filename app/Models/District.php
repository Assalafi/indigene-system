<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use UsesUuidV7;

    protected $fillable = [
        'lga_id', 'code', 'name', 'status', 'source_name', 'source_reference',
        'effective_from', 'effective_to', 'created_by', 'updated_by',
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

    public function wards()
    {
        return $this->hasMany(Ward::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
