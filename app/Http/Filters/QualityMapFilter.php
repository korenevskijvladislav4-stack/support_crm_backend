<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class QualityMapFilter extends AbstractFilter
{
    public const SEARCH = 'search';
    public const TEAM_ID = 'team_id';
    public const USER_ID = 'user_id';
    public const GROUP_ID = 'group_id';
    public const CHECKER_ID = 'checker_id';
    public const START_DATE = 'start_date';

    protected function getCallbacks(): array
    {
        return [
            self::SEARCH => [$this, 'search'],
            self::TEAM_ID => [$this, 'teamId'],
            self::USER_ID => [$this, 'userId'],
            self::GROUP_ID => [$this, 'groupId'],
            self::CHECKER_ID => [$this, 'checkerId'],
            self::START_DATE => [$this, 'startDate'],
        ];
    }

    public function search(Builder $builder, $value)
    {
        // Поиск по имени сотрудника
        $searchTerm = trim($value);
        if (!empty($searchTerm)) {
            $builder->whereHas('user', function ($query) use ($searchTerm) {
                $parts = explode(' ', $searchTerm, 2);
                
                if (count($parts) === 2) {
                    // Если есть два слова, ищем по имени и фамилии отдельно
                    $query->where(function ($q) use ($parts) {
                        $q->where('name', 'like', "%{$parts[0]}%")
                          ->where('surname', 'like', "%{$parts[1]}%");
                    })->orWhere(function ($q) use ($parts) {
                        // Также проверяем обратный порядок
                        $q->where('name', 'like', "%{$parts[1]}%")
                          ->where('surname', 'like', "%{$parts[0]}%");
                    });
                } else {
                    // Если одно слово, ищем в имени или фамилии
                    $query->where(function ($q) use ($searchTerm) {
                        $q->where('name', 'like', "%{$searchTerm}%")
                          ->orWhere('surname', 'like', "%{$searchTerm}%");
                    });
                }
            });
        }
    }

    public function teamId(Builder $builder, $value)
    {
        if (!empty($value)) {
            $builder->where('team_id', $value);
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

    public function checkerId(Builder $builder, $value)
    {
        if (!empty($value)) {
            $builder->where('checker_id', $value);
        }
    }

    public function startDate(Builder $builder, $value)
    {
        if (!empty($value)) {
            $builder->whereDate('start_date', $value);
        }
    }
}

