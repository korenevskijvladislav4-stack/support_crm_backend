<?php

namespace Database\Seeders;

use App\Models\TicketType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CreateDefaultTicketTypes extends Seeder
{
    public function run()
    {
        $types = [
            [
                'name' => 'Техническая проблема',
                'description' => 'Проблемы с оборудованием или ПО',
                'fields' => [
                    [
                        'name' => 'device_type',
                        'type' => 'select',
                        'label' => 'Тип устройства',
                        'required' => true,
                        'options' => ['Компьютер', 'Ноутбук', 'Телефон', 'Принтер', 'Другое']
                    ],
                    [
                        'name' => 'error_description',
                        'type' => 'textarea',
                        'label' => 'Описание ошибки',
                        'required' => true
                    ]
                ]
            ],
            [
                'name' => 'Запрос доступа',
                'description' => 'Запрос на предоставление прав доступа',
                'fields' => [
                    [
                        'name' => 'required_access',
                        'type' => 'text',
                        'label' => 'Требуемый доступ',
                        'required' => true
                    ],
                    [
                        'name' => 'reason',
                        'type' => 'textarea',
                        'label' => 'Обоснование',
                        'required' => true
                    ]
                ]
            ]
        ];

        foreach ($types as $type) {
            TicketType::create($type);
        }
    }
}
