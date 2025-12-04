<?php

namespace App\Http\Controllers;

use App\Http\Requests\Team\StoreRequest;
use App\Http\Requests\Team\UpdateRequest;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use App\Services\Team\Service;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для работы с командами
 */
class TeamController extends Controller
{
    /**
     * @var Service Сервис для работы с командами
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
     * Получить список всех команд
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $teams = $this->service->getAllWithRoles();
        return response()->json(TeamResource::collection($teams));
    }

    /**
     * Создать новую команду
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $team = $this->service->store($data);
        
        return response()->json([
            'message' => 'Team created successfully',
            'data' => new TeamResource($team)
        ], 201);
    }

    /**
     * Обновить команду
     *
     * @param UpdateRequest $request
     * @param Team $team
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, Team $team): JsonResponse
    {
        $data = $request->validated();
        $team = $this->service->update($team, $data);
        
        return response()->json([
            'message' => 'Team updated successfully',
            'data' => new TeamResource($team)
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Удалить команду
     *
     * @param Team $team
     * @return JsonResponse
     */
    public function destroy(Team $team): JsonResponse
    {
        $this->service->destroy($team);
        
        return response()->json([
            'message' => 'Team deleted successfully'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}

