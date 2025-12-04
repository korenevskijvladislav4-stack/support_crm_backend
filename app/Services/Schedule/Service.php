<?php

namespace App\Services\Schedule;

use App\Http\Resources\UserResource;
use App\Models\Group;
use App\Models\Shift;
use App\Models\User;
use App\Models\UserShift;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Service
{
    public function generateShifts($year, $month)
    {
        $shifts = [];
        $startDate = new Carbon($year . '-' . $month . '-01');
        $currentDate = $startDate->copy();
        $endDate = $startDate->copy()->endOfMonth();

        // Получаем все существующие смены для этого месяца
        $existingShifts = Shift::whereBetween('date', [
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        ])->pluck('date')->map(function ($date) {
            return Carbon::parse($date)->format('Y-m-d');
        })->toArray();

        while ($currentDate->lte($endDate)) {
            $dateString = $currentDate->format('Y-m-d');

            // Проверяем, существует ли уже смена на эту дату
            if (!in_array($dateString, $existingShifts)) {
                $shifts[] = [
                    'date' => $dateString,
                ];
            }

            $currentDate->addDay();
        }

        if (empty($shifts)) {
            return "Все смены за указанный месяц уже существуют";
        }

        $inserted = Shift::insert($shifts);

        return $inserted
            ? "Успешно создано " . count($shifts) . " новых смен"
            : "Ошибка при создании смен";
    }

    public function assignShiftsToUser($user, $startDate)
    {
        $workingDates = $user->scheduleType->name == '2/2'
            ? $this->getAgentSchedule($startDate)
            : $this->getAdminSchedule($startDate);

        $shiftIds = [];
        $alreadyAssignedShifts = []; // Для хранения уже назначенных смен

        foreach ($workingDates as $date) {
            $shift = Shift::whereDate('date', Carbon::parse($date))->first();

            if ($shift) {
                // Проверяем, есть ли уже такая смена у пользователя (включая удаленные)
                $existingShift = UserShift::withTrashed()
                    ->where('user_id', $user->id)
                    ->where('shift_id', $shift->id)
                    ->first();

                if (!$existingShift) {
                    // Смены нет вообще - добавляем
                    $shiftIds[] = [
                        'shift_id' => $shift->id,
                    ];
                } elseif ($existingShift->trashed()) {
                    // Смена была удалена индивидуально - не создаем её заново
                    // Пропускаем эту дату
                } else {
                    // Смена уже существует и не удалена
                    $alreadyAssignedShifts[] = $shift->date;
                }
            }
        }

        if (!empty($shiftIds)) {
            // Используем attach с дополнительными данными для создания записей с id
            foreach ($shiftIds as $shiftData) {
                $user->shifts()->attach($shiftData['shift_id'], [
                    'duration' => 12,
                    'is_active' => true,
                    'status' => UserShift::STATUS_APPROVED,
                    'is_viewed' => false,
                    'is_requested' => false, // Стандартная смена
                ]);
            }
        }

        $responseMessage = "Смены успешно назначены пользователю!";

        if (!empty($alreadyAssignedShifts)) {
            $responseMessage .= " Некоторые смены уже были назначены: "
                . implode(', ', $alreadyAssignedShifts);
        }

        return $responseMessage;
    }


    public function getAgentSchedule($startDate)
    {
        $workingDays = [];
        $startDate = new Carbon($startDate);
        $currentDate = $startDate->copy();
        $endDate = $startDate->copy()->endOfMonth();
        while ($currentDate->lte($endDate)) {
            array_push($workingDays, $currentDate->format('Y-m-d'));
            $currentDate = $currentDate->addDay();
            array_push($workingDays, $currentDate->format('Y-m-d'));
            $currentDate = $currentDate->addDays(3);
        }
        return $workingDays;
    }

    public function getAdminSchedule($startDate)
    {
        $workingDays = [];
        $startDate = new Carbon($startDate);
        $currentDate = $startDate->copy();
        $endDate = $startDate->copy()->endOfMonth();
        while ($currentDate->lte($endDate)) {
            if ($currentDate->dayName !== 'Saturday' && $currentDate->dayName !== 'Sunday') {
                array_push($workingDays, $currentDate->format('Y-m-d'));
            }
            $currentDate = $currentDate->addDay();
        }
        return $workingDays;
    }

    /**
     * Получить расписание для указанного периода
     *
     * @param int $year Год
     * @param int $month Месяц
     * @param int $team ID команды
     * @param string $shiftType Тип смены
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSchedule(int $year, int $month, int $team, string $shiftType)
    {
        return Group::query()
            ->where([
                ['shift_type', $shiftType],
                ['team_id', $team]
            ])
            ->with([
                'users' => fn($q) => $q->select('id', 'name', 'surname', 'team_id', 'group_id', 'email')
                    ->withTrashed()
                    ->whereHas('shifts', function ($q) use ($year, $month) {
                        $q->whereYear('shifts.date', $year)
                            ->whereMonth('shifts.date', $month)
                            ->whereNull('user_shifts.deleted_at'); // Только не удаленные смены
                    })
                    ->with([
                        'shifts' => fn($q) => $q->select('shifts.id', 'shifts.date')
                            ->whereYear('shifts.date', $year)
                            ->whereMonth('shifts.date', $month)
                            ->wherePivot('status', '!=', UserShift::STATUS_REJECTED) // Исключаем отклоненные смены
                            ->wherePivotNull('deleted_at') // Исключаем удаленные смены (soft deleted)
                            ->withPivot('id', 'duration', 'is_active', 'status')
                    ])
            ])
            ->orderBy('shift_number')
            ->get();
    }

    /**
     * Создать расписание для команды
     *
     * @param array $data Данные для создания расписания
     * @return array Результат создания
     */
    public function createSchedule(array $data): array
    {
        $users = \App\Models\User::whereHas('group', function ($q) use ($data) {
            $q->where('team_id', $data['team_id']);
        })->get();

        [$year, $month] = explode('-', $data['top_start'] ?? now()->format('Y-m'));

        $this->generateShifts($year, $month);

        foreach ($users as $user) {
            // Для графика 5/2 не учитываем верхнюю/нижнюю смену - это просто будние дни
            // Для графика 2/2 учитываем верхнюю/нижнюю смену
            if ($user->scheduleType->name == '2/2') {
                $start = $user->group->shift_number == 'Top'
                    ? $data['top_start']
                    : $data['bottom_start'];
            } else {
                // Для графика 5/2 используем одну дату начала для всех
                $start = $data['top_start'] ?? $data['bottom_start'];
            }
            $this->assignShiftsToUser($user, $start);
        }

        return ['success' => true];
    }

    /**
     * Удалить все смены пользователя начиная с указанной даты (включительно)
     *
     * @param User $user
     * @param string $date Дата (формат: Y-m-d)
     * @return void
     */
    public function deleteUserShiftsAfterDate(User $user, string $date): void
    {
        $dateCarbon = Carbon::parse($date)->startOfDay();
        $now = Carbon::now();

        // Массовое удаление (soft delete) всех смен пользователя начиная с указанной даты (включительно)
        // Используем прямой SQL запрос для надежности
        DB::table('user_shifts')
            ->where('user_id', $user->id)
            ->whereNull('deleted_at') // Только не удаленные смены
            ->whereIn('shift_id', function ($query) use ($dateCarbon) {
                $query->select('id')
                    ->from('shifts')
                    ->whereDate('date', '>=', $dateCarbon->format('Y-m-d'));
            })
            ->update([
                'deleted_at' => $now
            ]);
    }

    /**
     * Сгенерировать график для пользователя на основе стандартных смен группы
     * Учитывает schedule_type пользователя и удаленные смены других пользователей группы
     *
     * @param User $user Пользователь, для которого генерируется график
     * @param int $groupId ID новой группы
     * @param string $transferDate Дата перевода (формат: Y-m-d)
     * @return void
     */
    public function generateScheduleFromGroup(User $user, int $groupId, string $transferDate): void
    {
        $transferDateCarbon = Carbon::parse($transferDate);
        $endDate = $transferDateCarbon->copy()->endOfMonth();

        // Получаем группу
        $group = Group::findOrFail($groupId);

        // Получаем других пользователей группы с таким же schedule_type
        $otherUsers = User::where('group_id', $groupId)
            ->where('schedule_type_id', $user->schedule_type_id)
            ->where('id', '!=', $user->id)
            ->get();

        if ($otherUsers->isEmpty()) {
            // Если в группе нет других пользователей с таким же schedule_type,
            // генерируем график на основе типа графика пользователя
            $this->assignShiftsToUser($user, $transferDate);
            return;
        }

        // Получаем стандартные (не запрошенные) смены других пользователей группы
        // Стандартная смена = is_requested = false, status = 'approved', is_active = true
        // Используем withoutTrashed() чтобы получить только активные смены
        // Дополнительно проверяем через whereHas, что пользователь действительно в нужной группе
        $otherUserIds = $otherUsers->pluck('id')->toArray();

        $standardShifts = UserShift::withoutTrashed()
            ->whereIn('user_id', $otherUserIds)
            ->where('status', UserShift::STATUS_APPROVED)
            ->where('is_active', true)
            ->where('is_requested', false) // Только стандартные смены
            ->whereHas('user', function ($query) use ($groupId) {
                $query->where('group_id', $groupId);
            })
            ->whereHas('shift', function ($query) use ($transferDateCarbon, $endDate) {
                $query->whereDate('date', '>=', $transferDateCarbon->format('Y-m-d'))
                    ->whereDate('date', '<=', $endDate->format('Y-m-d'));
            })
            ->with('shift')
            ->get();

        // Получаем удаленные смены других пользователей группы (включая soft deleted)
        // Эти смены тоже нужно добавить в график новичку
        $deletedShifts = UserShift::withTrashed()
            ->whereIn('user_id', $otherUserIds)
            ->whereNotNull('deleted_at')
            ->where('is_requested', false) // Только стандартные удаленные смены
            ->whereHas('user', function ($query) use ($groupId) {
                $query->where('group_id', $groupId);
            })
            ->whereHas('shift', function ($query) use ($transferDateCarbon, $endDate) {
                $query->whereDate('date', '>=', $transferDateCarbon->format('Y-m-d'))
                    ->whereDate('date', '<=', $endDate->format('Y-m-d'));
            })
            ->with('shift')
            ->get();

        // Объединяем стандартные и удаленные смены
        $allReferenceShifts = $standardShifts->merge($deletedShifts);

        if ($allReferenceShifts->isEmpty()) {
            // Если нет стандартных смен, генерируем график на основе типа графика пользователя
            $this->assignShiftsToUser($user, $transferDate);
            return;
        }

        // Группируем смены по датам
        $shiftsByDate = $allReferenceShifts->groupBy(function ($userShift) {
            return Carbon::parse($userShift->shift->date)->format('Y-m-d');
        });

        // Получаем даты, где у пользователя были удалены индивидуально смены
        // Эти даты нужно исключить при генерации графика
        $userDeletedShiftDates = UserShift::withTrashed()
            ->where('user_id', $user->id)
            ->whereNotNull('deleted_at')
            ->whereHas('shift', function ($query) use ($transferDateCarbon, $endDate) {
                $query->whereDate('date', '>=', $transferDateCarbon->format('Y-m-d'))
                    ->whereDate('date', '<=', $endDate->format('Y-m-d'));
            })
            ->with('shift')
            ->get()
            ->map(function ($userShift) {
                return Carbon::parse($userShift->shift->date)->format('Y-m-d');
            })
            ->toArray();

        // Применяем смены к пользователю
        $assignedShifts = [];
        $transferDateStart = $transferDateCarbon->copy()->startOf('day');

        foreach ($shiftsByDate as $date => $userShifts) {
            $dateCarbon = Carbon::parse($date);

            // Пропускаем даты до transferDate - копируем только смены начиная с даты выхода
            if ($dateCarbon->lt($transferDateStart)) {
                continue;
            }

            // Пропускаем даты, где у пользователя была удалена индивидуально смена
            if (in_array($date, $userDeletedShiftDates)) {
                continue;
            }

            // Берем первую смену для этой даты
            $referenceShift = $userShifts->first();
            $shift = $referenceShift->shift;

            // Проверяем, есть ли уже такая активная (не удаленная) смена у пользователя
            $alreadyAssigned = UserShift::where('user_id', $user->id)
                ->where('shift_id', $shift->id)
                ->whereNull('deleted_at') // Только активные смены
                ->exists();

            if (!$alreadyAssigned) {
                // Присваиваем смену пользователю с теми же параметрами, что и у стандартной смены
                $user->shifts()->attach($shift->id, [
                    'duration' => $referenceShift->duration ?? 12,
                    'is_active' => true,
                    'status' => UserShift::STATUS_APPROVED,
                    'is_viewed' => false,
                    'is_requested' => false, // Стандартная смена
                ]);
                $assignedShifts[] = $date;
            }
        }

        // Если не удалось скопировать достаточно смен от других сотрудников группы,
        // дополняем график на основе типа графика пользователя, но только начиная с transferDate
        $daysRemaining = $transferDateCarbon->copy()->endOfMonth()->diffInDays($transferDateCarbon) + 1;
        $minShiftsNeeded = max(5, (int)($daysRemaining * 0.3)); // Минимум 30% от оставшихся дней или 5 смен

        if (count($assignedShifts) < $minShiftsNeeded) {
            // Дополняем график только если действительно не хватает смен
            $this->assignShiftsToUser($user, $transferDate);
        }
    }
}
