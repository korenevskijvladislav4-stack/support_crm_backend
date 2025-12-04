<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class AttemptFilter extends AbstractFilter
{
    public const SEARCH = 'search';
    public const EMAIL = 'email';
    public const PHONE = 'phone';
    public const CREATED_AT = 'created_at';
    public const IS_VIEWED = 'is_viewed';

    protected function getCallbacks(): array
    {
        return [
            self::SEARCH => [$this, 'search'],
            self::EMAIL => [$this, 'email'],
            self::PHONE => [$this, 'phone'],
            self::CREATED_AT => [$this, 'createdAt'],
            self::IS_VIEWED => [$this, 'isViewed'],
        ];
    }

    public function search(Builder $builder, $value)
    {
        // Поиск по имени и фамилии
        $searchTerm = trim($value);
        if (!empty($searchTerm)) {
            $parts = explode(' ', $searchTerm, 2);
            
            if (count($parts) === 2) {
                // Если есть два слова, ищем по имени и фамилии отдельно
                $builder->where(function ($query) use ($parts) {
                    $query->where(function ($subQuery) use ($parts) {
                        $subQuery->where('name', 'like', "%{$parts[0]}%")
                          ->where('surname', 'like', "%{$parts[1]}%");
                    })->orWhere(function ($subQuery) use ($parts) {
                        // Также проверяем обратный порядок
                        $subQuery->where('name', 'like', "%{$parts[1]}%")
                          ->where('surname', 'like', "%{$parts[0]}%");
                    });
                });
            } else {
                // Если одно слово, ищем в имени или фамилии
                $builder->where(function ($query) use ($searchTerm) {
                    $query->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('surname', 'like', "%{$searchTerm}%");
                });
            }
        }
    }

    public function email(Builder $builder, $value)
    {
        if (!empty($value)) {
            $builder->where('email', 'like', "%{$value}%");
        }
    }

    public function phone(Builder $builder, $value)
    {
        if (!empty($value)) {
            $builder->where('phone', 'like', "%{$value}%");
        }
    }

    public function createdAt(Builder $builder, $value)
    {
        if (!empty($value)) {
            $builder->whereDate('created_at', $value);
        }
    }

    public function isViewed(Builder $builder, $value)
    {
        // Обрабатываем boolean значения (true/false) и строки ("true"/"false")
        // Если значение уже boolean, используем его напрямую
        if (is_bool($value)) {
            $builder->where('is_viewed', $value);
        } elseif ($value !== null && $value !== '') {
            // Если строка, преобразуем в boolean
            $isViewed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isViewed !== null) {
                $builder->where('is_viewed', $isViewed);
            }
        }
    }
}

