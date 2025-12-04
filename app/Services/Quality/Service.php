<?php

namespace App\Services\Quality;

use App\Models\Quality;

/**
 * Сервис для работы с качеством
 */
class Service
{
    /**
     * Получить список всех записей качества
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return Quality::all();
    }

    /**
     * Получить запись качества с обзорами
     *
     * @param Quality $quality
     * @return Quality
     */
    public function getWithReviews(Quality $quality): Quality
    {
        return $quality->load('reviews');
    }

    /**
     * Создать новую запись качества
     *
     * @param array $data Данные качества
     * @return Quality Созданная запись
     */
    public function store(array $data): Quality
    {
        return Quality::firstOrCreate($data);
    }
}
