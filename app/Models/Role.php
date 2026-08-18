<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use UsesUuidV7;

    protected $fillable = ['name', 'guard_name', 'description', 'is_system'];
}
