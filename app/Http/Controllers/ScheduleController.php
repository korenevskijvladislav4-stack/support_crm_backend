<?php

namespace App\Http\Controllers;

use App\Http\Requests\Schedule\IndexRequest;
use App\Http\Requests\Schedule\StoreRequest;
use App\Http\Resources\GroupsWithUsersResource;
use App\Services\Schedule\Service;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для работы с расписанием
 */
class ScheduleController extends Controller
{
    /**
     * @var Service Сервис для работы с расписанием
     */
    protected Service $service;

    /**
     * Конструктор
     *
     * @param Service $service
     */
    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * Получить расписание за указанный месяц
     *
     * @param IndexRequest $request
     * @return JsonResponse
     */
    public function index(IndexRequest $request): JsonResponse
    {
        [$year, $month] = explode('-', $request->input('month', now()->format('Y-m')));
        $selectedTeam = (int) $request->input('team_id', 1);
        $selectedShiftType = $request->input('shift_type', "День");
        
        $groups = $this->service->getSchedule((int) $year, (int) $month, $selectedTeam, $selectedShiftType);
        $daysInMonth = Carbon::create((int) $year, (int) $month)->daysInMonth;

        return response()->json([
            'groups' => GroupsWithUsersResource::collection($groups),
            'days_in_month' => $daysInMonth,
        ]);
    }

    /**
     * Создать расписание для команды
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $result = $this->service->createSchedule($data);
        
        return response()->json([
            'message' => 'Schedule created successfully',
            'data' => $result
        ], 201);
    }
}
