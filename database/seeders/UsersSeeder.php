<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем 300 пользователей
        User::factory(300)->create();
        
        $this->command->info('Создано 300 пользователей');
    }
}

