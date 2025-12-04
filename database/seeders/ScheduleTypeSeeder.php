<?php

namespace Database\Seeders;

use App\Models\ScheduleType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ScheduleTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Проверяем, существуют ли уже типы графиков
        if (ScheduleType::where('name', '2 через 2')->doesntExist()) {
            ScheduleType::factory()->twoByTwo()->create();
        }

        if (ScheduleType::where('name', '5 через 2')->doesntExist()) {
            ScheduleType::factory()->fiveByTwo()->create();
        }
    }
}
