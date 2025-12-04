<?php

namespace App\Services\Group;

use App\Models\Group;

/**
 * Сервис для работы с группами
 */
class Service
{
    /**
     * Создать новую группу
     *
     * @param array $data Данные группы
     * @return Group Созданная группа
     */
    public function store(array $data): Group
    {
        $group = Group::create($data);
        return $group->load(['users', 'supervisor:id,name,surname']);
    }

    /**
     * Получить список всех групп с пользователями
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllWithUsers()
    {
        return Group::with(['users', 'supervisor:id,name,surname'])->get();
    }

    /**
     * Обновить группу
     *
     * @param Group $group
     * @param array $data Данные группы
     * @return Group Обновленная группа
     */
    public function update(Group $group, array $data): Group
    {
        $group->update($data);
        return $group->load(['users', 'supervisor:id,name,surname']);
    }

    /**
     * Удалить группу
     *
     * @param Group $group
     * @return bool
     */
    public function destroy(Group $group): bool
    {
        return $group->delete();
    }
}
