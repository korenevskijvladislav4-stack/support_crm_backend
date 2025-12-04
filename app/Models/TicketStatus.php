<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'order_index',
        'is_default'
    ];

    protected $casts = [
        'is_default' => 'boolean'
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
