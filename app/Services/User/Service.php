<?php

namespace App\Services\User;

use App\Models\User;
use App\Services\Schedule\Service as ScheduleService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class Service
{
    protected ScheduleService $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function update($user, $data){
        $roles = Arr::pull($data, 'roles', []);
        $user->update($data);
        $user->roles()->sync($roles);
        return $user;
    }
    
    public function destroy($user){
        $user->delete();
    }

    /**
     * Деактивировать пользователя
     * Завершает все сессии и помечает как удаленного
     *
     * @param User $user
     * @return User
     */
    public function deactivate(User $user): User
    {
        // Завершаем все токены доступа пользователя
        $user->tokens()->delete();
        
        // Завершаем все сессии пользователя (если используется database driver)
        // Проверяем наличие колонки user_id в таблице sessions
        if (DB::getSchemaBuilder()->hasColumn('sessions', 'user_id')) {
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->delete();
        }
        
        // Soft delete пользователя
        $user->delete();
        
        return $user;
    }

    /**
     * Активировать пользователя
     * Восстанавливает пользователя из soft delete
     *
     * @param User $user
     * @return User
     */
    public function activate(User $user): User
    {
        // Восстанавливаем пользователя
        $user->restore();
        
        return $user;
    }

    /**
     * Перевести пользователя в другую группу
     * Удаляет все смены после даты перевода и генерирует новые на основе стандартных смен новой группы
     *
     * @param User $user
     * @param int $newGroupId
     * @param string $transferDate Дата перевода (формат: Y-m-d)
     * @return User
     */
    public function transferGroup(User $user, int $newGroupId, string $transferDate): User
    {
        // Обновляем группу пользователя
        $user->group_id = $newGroupId;
        $user->save();

        // Удаляем все смены пользователя после даты перевода
        $this->scheduleService->deleteUserShiftsAfterDate($user, $transferDate);

        // Генерируем новый график на основе стандартных смен новой группы
        // с учетом schedule_type пользователя и удаленных смен
        $this->scheduleService->generateScheduleFromGroup($user, $newGroupId, $transferDate);
        
        return $user;
    }
}
