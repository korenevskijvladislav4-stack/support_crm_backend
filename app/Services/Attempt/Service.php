<?php

namespace App\Services\Attempt;

use App\Models\Attempt;
use App\Models\User;
use App\Services\Schedule\Service as ScheduleService;
use Illuminate\Support\Arr;

/**
 * Сервис для работы с попытками регистрации
 */
class Service
{
    /**
     * @var ScheduleService Сервис для работы с графиком
     */
    protected ScheduleService $scheduleService;

    /**
     * Конструктор
     *
     * @param ScheduleService $scheduleService
     */
    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Одобрить попытку регистрации и создать пользователя
     *
     * @param array $data Данные пользователя
     * @param Attempt $attempt Попытка регистрации
     * @return User Созданный пользователь
     */
    public function approve(array $data, Attempt $attempt): User
    {
        $roles = Arr::pull($data, 'roles', []);
        $startDate = Arr::pull($data, 'start_date');
        $groupId = Arr::get($data, 'group_id');
        $data['password'] = $attempt->password;
        
        // Если phone не передан в данных, берем из попытки
        if (!isset($data['phone']) && $attempt->phone) {
            $data['phone'] = $attempt->phone;
        }
        
        $user = User::create($data);
        $user->roles()->sync($roles);
        $attempt->update(['is_viewed' => 1]);
        
        // Генерируем график для нового сотрудника, если указаны группа и дата выхода
        if ($groupId && $startDate) {
            $this->scheduleService->generateScheduleFromGroup($user, $groupId, $startDate);
        }
        
        return $user;
    }

    /**
     * Получить список непросмотренных попыток
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUnwatchedAttempts()
    {
        return Attempt::where('is_viewed', '0')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Удалить попытку
     *
     * @param Attempt $attempt
     * @return bool
     */
    public function destroy(Attempt $attempt): bool
    {
        return $attempt->delete();
    }
}
