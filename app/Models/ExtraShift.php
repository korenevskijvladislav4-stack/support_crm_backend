<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExtraShift extends Model
{
    use softDeletes;
    protected $guarded = false;

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function shift(){
        return $this->belongsTo(Shift::class);
    }

    public function role(){
        return $this->belongsTo(Role::class);
    }
}
