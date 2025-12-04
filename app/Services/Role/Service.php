<?php

namespace App\Services\Role;

use App\Models\Role;
use Illuminate\Support\Arr;
use Spatie\Permission\Models\Permission;

/**
 * Сервис для работы с ролями
 */
class Service
{
    /**
     * Создать новую роль с правами
     *
     * @param array $data Данные роли
     * @return Role Созданная роль
     */
    public function store(array $data): Role
    {
        // Принудительно устанавливаем guard
        $data['guard_name'] = 'sanctum';

        // Извлекаем permissions
        $permissionNames = Arr::pull($data, 'permissions', []);

        // Создаём роль
        $role = Role::create($data);

        // Назначаем permissions
        if (!empty($permissionNames)) {
            $permissions = [];

            foreach ($permissionNames as $name) {
                $permissions[] = Permission::firstOrCreate(
                    ['name' => $name],
                    ['guard_name' => $data['guard_name']]
                );
            }

            $role->syncPermissions($permissions);
        }

        // Принудительный сброс кеша
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return $role->load('permissions');
    }

    /**
     * Получить список всех ролей с правами
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllWithPermissions()
    {
        return Role::with('permissions')->get();
    }

    /**
     * Обновить роль с правами
     *
     * @param Role $role
     * @param array $data Данные роли
     * @return Role Обновленная роль
     */
    public function update(Role $role, array $data): Role
    {
        // Принудительно устанавливаем guard
        $data['guard_name'] = 'sanctum';

        // Извлекаем permissions
        $permissionNames = Arr::pull($data, 'permissions', []);

        // Обновляем роль
        $role->update($data);

        // Обновляем permissions
        if (isset($permissionNames)) {
            $permissions = [];

            foreach ($permissionNames as $name) {
                $permissions[] = Permission::firstOrCreate(
                    ['name' => $name],
                    ['guard_name' => $data['guard_name']]
                );
            }

            $role->syncPermissions($permissions);
        }

        // Принудительный сброс кеша
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return $role->load('permissions');
    }

    /**
     * Удалить роль
     *
     * @param Role $role
     * @return bool
     */
    public function destroy(Role $role): bool
    {
        return $role->delete();
    }
}
