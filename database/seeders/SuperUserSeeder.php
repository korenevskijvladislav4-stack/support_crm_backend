<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SuperUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Список всех разрешений в системе (на основе маршрутов)
        $permissions = [
            // Пользователи
            'users', 'users.create', 'users.read', 'users.update', 'users.delete', 'users.show',
            // Роли
            'roles', 'roles.create', 'roles.read', 'roles.update', 'roles.delete',
            // Команды
            'teams', 'teams.create', 'teams.read', 'teams.update', 'teams.delete',
            // Группы
            'groups', 'groups.create', 'groups.read', 'groups.update', 'groups.delete',
            // График
            'schedule', 'schedule.create', 'schedule.read', 'schedule.update', 'schedule.delete',
            // Тикеты
            'tickets', 'tickets.create', 'tickets.read', 'tickets.update', 'tickets.delete',
            // Качество
            'quality', 'quality.create', 'quality.read', 'quality.update', 'quality.delete',
            'quality-criteria', 'quality-criteria.create', 'quality-criteria.read', 'quality-criteria.update', 'quality-criteria.delete',
            'quality-maps', 'quality-maps.create', 'quality-maps.read', 'quality-maps.update', 'quality-maps.delete',
            'quality-deductions', 'quality-deductions.create', 'quality-deductions.read', 'quality-deductions.update', 'quality-deductions.delete',
            // Попытки
            'attempts', 'attempts.create', 'attempts.read', 'attempts.update', 'attempts.delete', 'attempts.approve',
            // Системные
            'system.admin', 'system.settings', 'system.backup',
            'permissions', 'permissions.read',
        ];

        // Создаем все разрешения, если их нет
        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'sanctum'],
                ['name' => $permissionName, 'guard_name' => 'sanctum']
            );
        }

        // Создаем или получаем роль Super Admin
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'Super Admin', 'guard_name' => 'sanctum'],
            ['name' => 'Super Admin', 'guard_name' => 'sanctum']
        );

        // Получаем все разрешения (включая только что созданные)
        $allPermissions = Permission::where('guard_name', 'sanctum')->get();
        
        // Назначаем все разрешения роли Super Admin
        $superAdminRole->syncPermissions($allPermissions);

        // Создаем супер-пользователя
        $superUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super',
                'surname' => 'Admin',
                'email' => 'admin@example.com',
                'phone' => '+79999999999',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Назначаем роль Super Admin пользователю
        if (!$superUser->hasRole('Super Admin')) {
            $superUser->assignRole('Super Admin');
        }

        $this->command->info('Super User created successfully!');
        $this->command->info('Email: admin@example.com');
        $this->command->info('Password: password');
        $this->command->info('Phone: +79999999999');
        $this->command->info('Role: Super Admin');
        $this->command->info('Permissions: ' . $allPermissions->count() . ' permissions assigned');
    }
}
