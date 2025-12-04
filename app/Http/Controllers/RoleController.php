<?php

namespace App\Http\Controllers;

use App\Http\Requests\Role\StoreRequest;
use App\Http\Requests\Role\UpdateRequest;
use App\Http\Resources\RolesResource;
use App\Models\Role;
use App\Services\Role\Service;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для работы с ролями
 */
class RoleController extends Controller
{
    /**
     * @var Service Сервис для работы с ролями
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
     * Получить список всех ролей с правами
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $roles = $this->service->getAllWithPermissions();
        return response()->json(RolesResource::collection($roles));
    }

    /**
     * Создать новую роль с правами
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $role = $this->service->store($data);
        
        return response()->json([
            'message' => 'Role created successfully',
            'data' => new RolesResource($role)
        ], 201);
    }

    /**
     * Обновить роль
     *
     * @param UpdateRequest $request
     * @param Role $role
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, Role $role): JsonResponse
    {
        $data = $request->validated();
        $role = $this->service->update($role, $data);
        
        return response()->json([
            'message' => 'Role updated successfully',
            'data' => new RolesResource($role)
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Удалить роль
     *
     * @param Role $role
     * @return JsonResponse
     */
    public function destroy(Role $role): JsonResponse
    {
        $this->service->destroy($role);
        
        return response()->json([
            'message' => 'Role deleted successfully'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}

