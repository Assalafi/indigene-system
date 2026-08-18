<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class AccountActivationToken extends Model
{
    use UsesUuidV7;

    protected $fillable = ['user_id', 'token_hash', 'expires_at', 'used_at', 'created_by'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
