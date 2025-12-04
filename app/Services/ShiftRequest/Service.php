<?php

namespace App\Services\ShiftRequest;

use App\Models\Shift;
use App\Models\UserShift;
use Illuminate\Support\Facades\DB;

/**
 * Сервис для работы с запросами дополнительных смен
 */
class Service
{
    /**
     * Создать запрос на дополнительную смену
     *
     * @param array $data Данные запроса
     * @return UserShift Созданный запрос
     */
    public function requestShift(array $data): UserShift
    {
        // Если user_id не указан, используем текущего пользователя
        if (!isset($data['user_id'])) {
            $data['user_id'] = auth()->id();
        }

        // Если передан date вместо shift_id, находим или создаем смену
        if (isset($data['date']) && !isset($data['shift_id'])) {
            $shift = Shift::firstOrCreate(
                ['date' => $data['date']],
                ['date' => $data['date']]
            );
            $data['shift_id'] = $shift->id;
        } else {
            // Проверяем, существует ли смена
            $shift = Shift::findOrFail($data['shift_id']);
        }

        // Проверяем, не запрошена ли уже эта смена этим пользователем
        $existingRequest = UserShift::where('user_id', $data['user_id'])
            ->where('shift_id', $data['shift_id'])
            ->where('status', UserShift::STATUS_PENDING)
            ->first();

        if ($existingRequest) {
            throw new \Exception('Запрос на эту смену уже существует');
        }

        // Проверяем, не назначена ли уже эта смена этому пользователю
        $existingShift = UserShift::where('user_id', $data['user_id'])
            ->where('shift_id', $data['shift_id'])
            ->where('status', UserShift::STATUS_APPROVED)
            ->first();

        if ($existingShift) {
            throw new \Exception('Эта смена уже назначена вам');
        }

        // Создаем запрос со статусом pending
        return UserShift::create([
            'user_id' => $data['user_id'],
            'shift_id' => $data['shift_id'],
            'duration' => $data['duration'] ?? 12,
            'status' => UserShift::STATUS_PENDING,
            'is_active' => false, // Не активна до одобрения
            'is_viewed' => false,
            'is_requested' => true, // Запрошенная смена
        ]);
    }

    /**
     * Одобрить запрос на смену
     *
     * @param UserShift $userShift
     * @return UserShift
     */
    public function approve(UserShift $userShift): UserShift
    {
        // Обновляем через DB, чтобы избежать проблем с timestamps
        DB::table('user_shifts')
            ->where('id', $userShift->id)
            ->update([
                'status' => UserShift::STATUS_APPROVED,
                'is_active' => true,
            ]);

        // Перезагружаем модель из базы данных
        $userShift = UserShift::find($userShift->id);
        
        if (!$userShift) {
            throw new \Exception('Не удалось найти обновленную смену');
        }
        
        return $userShift;
    }

    /**
     * Отклонить запрос на смену
     *
     * @param UserShift $userShift
     * @return bool
     */
    public function reject(UserShift $userShift): bool
    {
        // Обновляем через DB, чтобы избежать проблем с timestamps
        $updated = DB::table('user_shifts')
            ->where('id', $userShift->id)
            ->update([
                'status' => UserShift::STATUS_REJECTED,
                'is_active' => false,
            ]);
        
        return $updated > 0;
    }

    /**
     * Получить все запрошенные смены пользователя
     *
     * @param int|null $userId ID пользователя (если null - текущий пользователь)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserRequests(?int $userId = null)
    {
        $userId = $userId ?? auth()->id();
        
        return UserShift::where('user_id', $userId)
            ->where('status', UserShift::STATUS_PENDING)
            ->with(['shift', 'user'])
            ->get();
    }

    /**
     * Редактировать смену (изменить продолжительность)
     *
     * @param UserShift $userShift
     * @param int $duration Новая продолжительность
     * @return UserShift
     */
    public function update(UserShift $userShift, int $duration): UserShift
    {
        // Максимальная продолжительность смены - 24 часа
        if ($duration > 24) {
            throw new \Exception('Максимальная продолжительность смены не может превышать 24 часа');
        }

        if ($duration < 1) {
            throw new \Exception('Минимальная продолжительность смены - 1 час');
        }

        // Проверяем, что запись существует
        if (!$userShift->exists) {
            throw new \Exception('Запись не найдена в базе данных');
        }

        $id = $userShift->id;
        
        // Проверяем, что запись действительно существует в БД
        $exists = DB::table('user_shifts')->where('id', $id)->exists();
        if (!$exists) {
            throw new \Exception("Запись с ID {$id} не существует в базе данных");
        }

        // Обновляем продолжительность напрямую через DB, чтобы избежать проблем с timestamps
        $updated = DB::table('user_shifts')
            ->where('id', $id)
            ->update(['duration' => $duration]);

        if ($updated === 0) {
            // Проверяем текущее значение duration
            $currentDuration = DB::table('user_shifts')
                ->where('id', $id)
                ->value('duration');
            
            throw new \Exception("Не удалось обновить смену. ID: {$id}, Текущая продолжительность: {$currentDuration}, Новая: {$duration}");
        }

        // Перезагружаем модель из базы данных
        $userShift = UserShift::find($id);
        
        if (!$userShift) {
            throw new \Exception('Не удалось найти обновленную смену');
        }

        return $userShift;
    }

    /**
     * Удалить смену (soft delete)
     *
     * @param UserShift $userShift
     * @return bool
     */
    public function destroy(UserShift $userShift): bool
    {
        // Проверяем, что запись существует
        if (!$userShift->exists) {
            throw new \Exception('Запись не найдена в базе данных');
        }
        
        // Используем soft delete
        $userShift->delete();
        
        return true;
    }

    /**
     * Создать смену напрямую без запроса (одобрена сразу)
     *
     * @param array $data Данные смены
     * @return UserShift Созданная смена
     */
    public function createDirect(array $data): UserShift
    {
        // Если передан date вместо shift_id, находим или создаем смену
        if (isset($data['date']) && !isset($data['shift_id'])) {
            $shift = Shift::firstOrCreate(
                ['date' => $data['date']],
                ['date' => $data['date']]
            );
            $data['shift_id'] = $shift->id;
        } else {
            // Проверяем, существует ли смена
            $shift = Shift::findOrFail($data['shift_id']);
        }

        // Проверяем, не назначена ли уже эта смена этому пользователю
        $existingShift = UserShift::where('user_id', $data['user_id'])
            ->where('shift_id', $data['shift_id'])
            ->where('status', UserShift::STATUS_APPROVED)
            ->first();

        if ($existingShift) {
            throw new \Exception('Эта смена уже назначена пользователю');
        }

        // Удаляем существующий запрос на эту смену, если есть
        UserShift::where('user_id', $data['user_id'])
            ->where('shift_id', $data['shift_id'])
            ->where('status', UserShift::STATUS_PENDING)
            ->delete();

        // Создаем смену со статусом approved
        return UserShift::create([
            'user_id' => $data['user_id'],
            'shift_id' => $data['shift_id'],
            'duration' => $data['duration'] ?? 12,
            'status' => UserShift::STATUS_APPROVED,
            'is_active' => true,
            'is_viewed' => false,
            'is_requested' => true, // Прямая смена тоже считается запрошенной
        ]);
    }
}

