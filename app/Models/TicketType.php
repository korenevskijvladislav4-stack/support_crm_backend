<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'fields',
        'is_active'
    ];

    protected $casts = [
        'fields' => 'array',
        'is_active' => 'boolean'
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
