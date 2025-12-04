<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Создаем типы графиков
        $this->call([
            ScheduleTypeSeeder::class,
        ]);
        
        // Создаем 300 пользователей (раскомментируйте при необходимости)
        // $this->call([
        //     UsersSeeder::class,
        // ]);
    }
}
