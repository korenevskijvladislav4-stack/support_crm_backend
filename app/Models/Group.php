<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $guarded = false;
    
    public function users(){
        return $this->hasMany(User::class);
    }
    
    public function team(){
        return $this->belongsTo(Team::class);
    }
    
    public function supervisor(){
        return $this->belongsTo(User::class, 'supervisor_id');
    }
}
