<?php

namespace App\Http\Controllers;

use App\Http\Requests\Group\StoreRequest;
use App\Http\Requests\Group\UpdateRequest;
use App\Http\Resources\GroupsResource;
use App\Models\Group;
use App\Services\Group\Service;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для работы с группами
 */
class GroupController extends Controller
{
    /**
     * @var Service Сервис для работы с группами
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
     * Получить список всех групп с пользователями
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $groups = $this->service->getAllWithUsers();
        return response()->json(GroupsResource::collection($groups));
    }

    /**
     * Создать новую группу
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $group = $this->service->store($data);
        
        return response()->json([
            'message' => 'Group created successfully',
            'data' => new GroupsResource($group)
        ], 201);
    }

    /**
     * Обновить группу
     *
     * @param UpdateRequest $request
     * @param Group $group
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, Group $group): JsonResponse
    {
        $data = $request->validated();
        $group = $this->service->update($group, $data);
        
        return response()->json([
            'message' => 'Group updated successfully',
            'data' => new GroupsResource($group)
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Удалить группу
     *
     * @param Group $group
     * @return JsonResponse
     */
    public function destroy(Group $group): JsonResponse
    {
        $this->service->destroy($group);
        
        return response()->json([
            'message' => 'Group deleted successfully'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
