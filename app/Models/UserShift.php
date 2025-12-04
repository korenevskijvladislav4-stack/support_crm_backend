<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserShift extends Model
{
    use SoftDeletes;
    
    // Отключаем timestamps, так как они могут отсутствовать в таблице
    // SoftDeletes будет работать с deleted_at независимо от timestamps
    public $timestamps = false;
    
    protected $guarded = false;

    protected $fillable = [
        'user_id',
        'shift_id',
        'duration',
        'is_active',
        'is_viewed',
        'status',
        'is_requested',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_viewed' => 'boolean',
        'is_requested' => 'boolean',
        'duration' => 'integer',
    ];

    // Статусы смен
    const STATUS_APPROVED = 'approved';
    const STATUS_PENDING = 'pending';
    const STATUS_REJECTED = 'rejected';

    /**
     * Связь с пользователем
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Связь со сменой
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    /**
     * Проверить, является ли смена запрошенной (ожидает одобрения)
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Проверить, одобрена ли смена
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Проверить, отклонена ли смена
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }
}

