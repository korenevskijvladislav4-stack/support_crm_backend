<?php

namespace App\Models;

use App\Models\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Attempt extends Authenticatable
{
    use Filterable;
    
    protected $table = 'attempts';
    protected $guarded = false;
    
    protected $casts = [
        'is_viewed' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
