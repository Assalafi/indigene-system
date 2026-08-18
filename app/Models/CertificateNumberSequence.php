<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class CertificateNumberSequence extends Model
{
    use UsesUuidV7;

    public const CREATED_AT = null;

    protected $fillable = ['lga_id', 'year', 'prefix', 'next_value', 'padding'];

    public function lga()
    {
        return $this->belongsTo(Lga::class);
    }
}
