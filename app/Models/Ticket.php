<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'title',
        'description',
        'type_id',
        'status_id',
        'priority',
        'creator_id',
        'team_id',
        'custom_fields'
    ];

    protected $casts = [
        'custom_fields' => 'array'
    ];

    // Генерация номера тикета
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (!$ticket->ticket_number) {
                $ticket->ticket_number = 'TICKET-' . date('Ymd') . '-' . str_pad(static::count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // Отношения
    public function type()
    {
        return $this->belongsTo(TicketType::class);
    }

    public function status()
    {
        return $this->belongsTo(TicketStatus::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class)->orderBy('created_at', 'desc');
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function activities()
    {
        return $this->hasMany(TicketActivity::class)->orderBy('created_at', 'desc');
    }
}
