<?php

namespace Database\Seeders;

use App\Models\TicketStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CreateDefaultTicketStatuses extends Seeder
{
    public function run()
    {
        $statuses = [
            ['name' => 'Новый', 'color' => '#1890ff', 'order_index' => 1, 'is_default' => true],
            ['name' => 'В работе', 'color' => '#faad14', 'order_index' => 2],
            ['name' => 'На паузе', 'color' => '#ff4d4f', 'order_index' => 3],
            ['name' => 'Решен', 'color' => '#52c41a', 'order_index' => 4],
            ['name' => 'Закрыт', 'color' => '#d9d9d9', 'order_index' => 5],
        ];

        foreach ($statuses as $status) {
            TicketStatus::create($status);
        }
    }
}
