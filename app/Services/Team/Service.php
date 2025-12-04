<?php

namespace App\Services\Team;

use App\Models\Team;

/**
 * Сервис для работы с командами
 */
class Service
{
    /**
     * Создать новую команду
     *
     * @param array $data Данные команды
     * @return Team Созданная команда
     */
    public function store(array $data): Team
    {
        $roles = $data['role_id'] ?? [];
        unset($data['role_id']);
        
        $team = Team::create($data);
        if (!empty($roles)) {
            $team->roles()->sync($roles);
        }
        
        return $team->load('roles');
    }

    /**
     * Получить список всех команд с ролями
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllWithRoles()
    {
        return Team::with('roles')->get();
    }

    /**
     * Обновить команду
     *
     * @param Team $team
     * @param array $data Данные команды
     * @return Team Обновленная команда
     */
    public function update(Team $team, array $data): Team
    {
        $roles = $data['role_id'] ?? [];
        unset($data['role_id']);
        
        $team->update($data);
        if (isset($roles)) {
            $team->roles()->sync($roles);
        }
        
        return $team->load('roles');
    }

    /**
     * Удалить команду
     *
     * @param Team $team
     * @return bool
     */
    public function destroy(Team $team): bool
    {
        return $team->delete();
    }
}
