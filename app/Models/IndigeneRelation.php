<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class IndigeneRelation extends Model
{
    use UsesUuidV7;

    protected $fillable = [
        'profile_id', 'relation_type', 'full_name', 'relationship', 'phone', 'email', 'address',
        'occupation', 'state_id', 'lga_id', 'ward_id', 'unit_id', 'nin_ciphertext', 'nin_hash',
        'nin_last4', 'is_primary',
    ];

    protected $hidden = ['nin_ciphertext'];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function profile()
    {
        return $this->belongsTo(IndigeneProfile::class, 'profile_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function lga()
    {
        return $this->belongsTo(Lga::class);
    }

    public function relationTypeLabel(): string
    {
        return str_replace('_', ' ', ucfirst($this->relation_type));
    }
}
