<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $guard_name = 'sanctum';
    protected $guarded = false;
    public function teams(){
        return $this->belongsToMany(Team::class, "team_roles");
    }
}
