<?php

namespace App\Services\Penalty;

use App\Models\Penalty;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class Service
{
    /**
     * Создать штраф
     *
     * @param array $data
     * @param int $createdBy
     * @return Penalty
     */
    public function store(array $data, int $createdBy): Penalty
    {
        $data['created_by'] = $createdBy;
        $data['status'] = Penalty::STATUS_PENDING;
        
        return Penalty::create($data);
    }

    /**
     * Обновить штраф
     *
     * @param Penalty $penalty
     * @param array $data
     * @return Penalty
     */
    public function update(Penalty $penalty, array $data): Penalty
    {
        $penalty->update($data);
        
        // Если статус изменился на одобрен, снимаем часы
        if (isset($data['status']) && $data['status'] === Penalty::STATUS_APPROVED && !$penalty->isApproved()) {
            $this->deductHours($penalty);
        }
        
        return $penalty->fresh();
    }

    /**
     * Одобрить штраф и снять часы
     *
     * @param Penalty $penalty
     * @return Penalty
     */
    public function approve(Penalty $penalty): Penalty
    {
        // Если уже одобрен, ничего не делаем
        if ($penalty->isApproved()) {
            return $penalty;
        }

        DB::transaction(function () use ($penalty) {
            $penalty->update(['status' => Penalty::STATUS_APPROVED]);
            // Снимаем часы только если штраф был в статусе pending или rejected
            // (для rejected снимаем часы при повторном одобрении)
            $this->deductHours($penalty);
        });

        return $penalty->fresh();
    }

    /**
     * Отклонить штраф
     *
     * @param Penalty $penalty
     * @return Penalty
     */
    public function reject(Penalty $penalty): Penalty
    {
        $penalty->update(['status' => Penalty::STATUS_REJECTED]);
        
        return $penalty->fresh();
    }

    /**
     * Снять рабочие часы с пользователя
     *
     * @param Penalty $penalty
     * @return void
     */
    protected function deductHours(Penalty $penalty): void
    {
        $user = $penalty->user;
        $hoursToDeduct = $penalty->hours_to_deduct;

        // Находим активные смены пользователя и снимаем часы
        // Сначала снимаем с самых ранних активных смен
        $userShifts = DB::table('user_shifts')
            ->join('shifts', 'user_shifts.shift_id', '=', 'shifts.id')
            ->where('user_shifts.user_id', $user->id)
            ->where('user_shifts.is_active', 1)
            ->where('user_shifts.status', '!=', 'rejected')
            ->where('shifts.date', '>=', now()->subMonths(3)->format('Y-m-d')) // Только за последние 3 месяца
            ->orderBy('shifts.date', 'asc')
            ->orderBy('user_shifts.id', 'asc')
            ->select('user_shifts.*', 'shifts.date')
            ->get();

        $remainingHours = $hoursToDeduct;

        foreach ($userShifts as $shift) {
            if ($remainingHours <= 0) {
                break;
            }

            $currentDuration = $shift->duration ?? 0;
            
            if ($currentDuration > 0) {
                $deductFromShift = min($remainingHours, $currentDuration);
                $newDuration = max(0, $currentDuration - $deductFromShift);
                
                DB::table('user_shifts')
                    ->where('id', $shift->id)
                    ->update([
                        'duration' => $newDuration
                    ]);
                
                $remainingHours -= $deductFromShift;
            }
        }

        // Если остались несписанные часы, можно залогировать или обработать иначе
        if ($remainingHours > 0) {
            \Log::warning("Не удалось списать все часы для штрафа #{$penalty->id}. Осталось: {$remainingHours} часов");
        }
    }
}

