<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $guarded = false;
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_shifts');
    }
}
