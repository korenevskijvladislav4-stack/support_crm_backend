<?php

namespace App\Models;

use App\Models\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;

class QualityMap extends Model
{
    use Filterable;
    protected $fillable = [
        'user_id', 'checker_id', 'start_date', 'end_date',
        'team_id', 'chat_ids', 'call_ids', 'calls_count', 'total_score', 'comment'
    ];

    protected $casts = [
        'chat_ids' => 'array',
        'call_ids' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checker_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function deductions()
    {
        return $this->hasMany(QualityDeduction::class);
    }

    public function callDeductions()
    {
        return $this->hasMany(QualityCallDeduction::class);
    }

    /**
     * Пересчитать общий балл карты качества
     * Учитывает все чаты и звонки
     */
    public function recalculateTotalScore(): void
    {
        $allChatIds = $this->chat_ids ?? [];
        $allCallIds = $this->call_ids ?? [];
        
        // Получаем заполненные чаты
        $filledChats = array_filter($allChatIds, function($chatId) {
            return !empty($chatId) && trim($chatId) !== '';
        });
        
        // Получаем заполненные звонки
        $filledCalls = array_filter($allCallIds, function($callId) {
            return !empty($callId) && trim($callId) !== '';
        });
        
        $totalScore = 0;
        $totalItems = 0;
        
        // Обрабатываем чаты
        foreach ($filledChats as $chatId) {
            $chatDeductions = $this->deductions()
                ->where('chat_id', $chatId)
                ->sum('deduction');
            
            $chatScore = max(0, 100 - $chatDeductions);
            $totalScore += $chatScore;
            $totalItems++;
        }
        
        // Обрабатываем звонки
        foreach ($filledCalls as $callId) {
            $callDeductions = $this->callDeductions()
                ->where('call_id', $callId)
                ->sum('deduction');
            
            $callScore = max(0, 100 - $callDeductions);
            $totalScore += $callScore;
            $totalItems++;
        }
        
        // Рассчитываем средний балл
        $averageScore = $totalItems > 0 ? round($totalScore / $totalItems) : 0;
        
        $this->update(['total_score' => $averageScore]);
    }
}
