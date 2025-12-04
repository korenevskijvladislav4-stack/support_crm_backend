<?php

namespace App\Services\ExtraShift;

use App\Models\ExtraShift;
use App\Models\UserShift;

/**
 * Сервис для работы с дополнительными сменами
 */
class Service
{
    /**
     * Получить список всех дополнительных смен
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return ExtraShift::all();
    }

    /**
     * Создать дополнительную смену
     *
     * @param array $data Данные смены
     * @return UserShift Созданная смена
     */
    public function store(array $data): UserShift
    {
        return UserShift::create($data);
    }

    /**
     * Одобрить дополнительную смену
     *
     * @param ExtraShift $extraShift
     * @return bool
     */
    public function approve(ExtraShift $extraShift): bool
    {
        $user = $extraShift->user;
        $shift = $extraShift->shift;
        
        if ($user && $shift) {
            $user->shifts()->attach($shift);
        }
        
        return $extraShift->delete();
    }

    /**
     * Отклонить дополнительную смену
     *
     * @param ExtraShift $extraShift
     * @return bool
     */
    public function reject(ExtraShift $extraShift): bool
    {
        return $extraShift->delete();
    }
}

