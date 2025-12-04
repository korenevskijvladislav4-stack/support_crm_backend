<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $guarded = false;

    public function criteria()
    {
        return $this->belongsToMany(QualityCriteria::class, 'criteria_team');
    }

    public function roles(){
        return $this->belongsToMany(Role::class,'team_roles');
    }
}
