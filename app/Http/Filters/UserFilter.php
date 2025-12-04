<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class UserFilter extends AbstractFilter
{
    public const EMAIL = 'email';
    public const FULL_NAME = 'full_name';
    public const GROUP = 'group';
    public const TEAM = 'team';
    public const ROLES = 'roles';
    public const SCHEDULE_TYPE = 'schedule_type';
    public const PHONE = 'phone';

    protected function getCallbacks(): array
    {
        return [
            self::EMAIL => [$this, 'email'],
            self::FULL_NAME => [$this, 'fullName'],
            self::GROUP => [$this, 'group'],
            self::TEAM => [$this, 'team'],
            self::ROLES => [$this, 'roles'],
            self::SCHEDULE_TYPE => [$this, 'scheduleType'],
            self::PHONE => [$this, 'phone'],
        ];
    }

    public function fullName(Builder $builder, $value)
    {
        // Оптимизированный поиск: используем отдельные условия для использования индексов
        $searchTerm = trim($value);
        $parts = explode(' ', $searchTerm, 2);
        
        if (count($parts) === 2) {
            // Если есть два слова, ищем по имени и фамилии отдельно
            $builder->where(function ($query) use ($parts) {
                $query->where('name', 'like', "%{$parts[0]}%")
                      ->where('surname', 'like', "%{$parts[1]}%");
            })->orWhere(function ($query) use ($parts) {
                // Также проверяем обратный порядок
                $query->where('name', 'like', "%{$parts[1]}%")
                      ->where('surname', 'like', "%{$parts[0]}%");
            });
        } else {
            // Если одно слово, ищем в имени или фамилии
            $builder->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('surname', 'like', "%{$searchTerm}%");
            });
        }
    }

    public function email(Builder $builder, $value)
    {
        $builder->where('email', 'like', "%{$value}%");
    }

    public function group(Builder $builder, $value)
    {
        // Используем прямое условие вместо whereHas для лучшей производительности
        if (!empty($value) && is_array($value)) {
            $builder->whereIn('group_id', $value);
        }
    }

    public function team(Builder $builder, $value)
    {
        // Используем прямое условие вместо whereHas для лучшей производительности
        if (!empty($value) && is_array($value)) {
            $builder->whereIn('team_id', $value);
        }
    }

    public function roles(Builder $builder, $value)
    {
        // Для ролей используем whereHas, так как это many-to-many через промежуточную таблицу
        if (!empty($value) && is_array($value)) {
            $builder->whereHas('roles', function ($query) use ($value) {
                $query->whereIn('roles.id', $value);
            });
        }
    }

    public function scheduleType(Builder $builder, $value)
    {
        // Фильтрация по имени типа графика через связь scheduleType
        if (!empty($value)) {
            $builder->whereHas('scheduleType', function ($query) use ($value) {
                $query->where('name', $value);
            });
        }
    }

    public function phone(Builder $builder, $value)
    {
        $builder->where('phone', 'like', "%{$value}%");
    }
}
