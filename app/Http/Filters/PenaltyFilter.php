<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class PenaltyFilter extends AbstractFilter
{
    public const SEARCH = 'search';
    public const USER_ID = 'user_id';
    public const GROUP_ID = 'group_id';
    public const CREATED_BY = 'created_by';
    public const CREATED_AT = 'created_at';

    protected function getCallbacks(): array
    {
        return [
            self::SEARCH => [$this, 'search'],
            self::USER_ID => [$this, 'userId'],
            self::GROUP_ID => [$this, 'groupId'],
            self::CREATED_BY => [$this, 'createdBy'],
            self::CREATED_AT => [$this, 'createdAt'],
        ];
    }

    public function search(Builder $builder, $value)
    {
        // Поиск по имени пользователя или создателя
        $searchTerm = trim($value);
        if (!empty($searchTerm)) {
            $builder->where(function ($query) use ($searchTerm) {
                $query->whereHas('user', function ($q) use ($searchTerm) {
                    $parts = explode(' ', $searchTerm, 2);
                    
                    if (count($parts) === 2) {
                        // Если есть два слова, ищем по имени и фамилии отдельно
                        $q->where(function ($subQuery) use ($parts) {
                            $subQuery->where('name', 'like', "%{$parts[0]}%")
                              ->where('surname', 'like', "%{$parts[1]}%");
                        })->orWhere(function ($subQuery) use ($parts) {
                            // Также проверяем обратный порядок
                            $subQuery->where('name', 'like', "%{$parts[1]}%")
                              ->where('surname', 'like', "%{$parts[0]}%");
                        });
                    } else {
                        // Если одно слово, ищем в имени или фамилии
                        $q->where(function ($subQuery) use ($searchTerm) {
                            $subQuery->where('name', 'like', "%{$searchTerm}%")
                              ->orWhere('surname', 'like', "%{$searchTerm}%");
                        });
                    }
                })->orWhereHas('creator', function ($q) use ($searchTerm) {
                    $parts = explode(' ', $searchTerm, 2);
                    
                    if (count($parts) === 2) {
                        $q->where(function ($subQuery) use ($parts) {
                            $subQuery->where('name', 'like', "%{$parts[0]}%")
                              ->where('surname', 'like', "%{$parts[1]}%");
                        })->orWhere(function ($subQuery) use ($parts) {
                            $subQuery->where('name', 'like', "%{$parts[1]}%")
                              ->where('surname', 'like', "%{$parts[0]}%");
                        });
                    } else {
                        $q->where(function ($subQuery) use ($searchTerm) {
                            $subQuery->where('name', 'like', "%{$searchTerm}%")
                              ->orWhere('surname', 'like', "%{$searchTerm}%");
                        });
                    }
                });
            });
        }
    }

    public function userId(Builder $builder, $value)
    {
        if (!empty($value)) {
            $builder->where('user_id', $value);
        }
    }

    public function groupId(Builder $builder, $value)
    {
        // Фильтрация по группе пользователя
        if (!empty($value)) {
            $builder->whereHas('user', function ($query) use ($value) {
                $query->where('group_id', $value);
            });
        }
    }

    public function createdBy(Builder $builder, $value)
    {
        if (!empty($value)) {
            $builder->where('created_by', $value);
        }
    }

    public function createdAt(Builder $builder, $value)
    {
        if (!empty($value)) {
            $builder->whereDate('created_at', $value);
        }
    }
}

