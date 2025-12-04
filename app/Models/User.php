<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Filterable;
    use softDeletes;
    use HasApiTokens;
    use HasRoles;
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    protected $guard_name = 'sanctum';
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = false;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function team(){
        return $this->belongsTo(Team::class);
    }
    public function shifts(){
        return $this->belongsToMany(Shift::class, 'user_shifts')
            ->withPivot('id', 'duration', 'is_active', 'status', 'is_viewed');
    }
    public function scheduleType(){
        return $this->belongsTo(ScheduleType::class);
    }
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    // Spatie Permission уже предоставляет метод roles() через HasRoles trait
    // Кастомный метод удален для избежания конфликтов
    public function qualities(){
        return $this->hasMany(Quality::class);
    }
    public function getFullNameAttribute()
    {
        return "{$this->name} {$this->surname}";
    }
}
