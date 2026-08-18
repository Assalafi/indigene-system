<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use UsesUuidV7;

    protected $fillable = [
        'code', 'name', 'type', 'capital', 'status',
        'source_name', 'source_reference', 'effective_from', 'effective_to',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    public function lgas()
    {
        return $this->hasMany(Lga::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function fullLabel(): string
    {
        return $this->type === 'fct' ? "{$this->name} (FCT)" : "{$this->name} State";
    }
}
