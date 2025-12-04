<?php

namespace App\Http\Controllers;

use App\Http\Filters\UserFilter;
use App\Http\Requests\User\FilterRequest;
use App\Http\Requests\User\TransferGroupRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\User\Service;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для работы с пользователями
 */
class UserController extends Controller
{
    /**
     * @var Service Сервис для работы с пользователями
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
     * Получить список пользователей с фильтрацией
     *
     * @param FilterRequest $request
     * @return JsonResponse
     */
    public function index(FilterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $filter = app()->make(UserFilter::class, ['queryParams' => array_filter($data)]);
        
        // Определяем, нужно ли показывать только активных или деактивированных
        $status = $request->input('status', 'active'); // 'active' или 'deactivated'
        
        $query = User::filter($filter);
        
        if ($status === 'deactivated') {
            // Показываем только деактивированных (soft deleted)
            $query->onlyTrashed();
        } else {
            // Показываем только активных (не удаленных)
            $query->withoutTrashed();
        }
        
        // Eager loading для предотвращения N+1 запросов
        $query->with([
            'roles:id,name',
            'team:id,name',
            'group:id,name',
            'scheduleType:id,name'
        ]);
        
        // Выбираем только необходимые поля для уменьшения объема данных
        $query->select([
            'users.id',
            'users.name',
            'users.surname',
            'users.email',
            'users.phone',
            'users.team_id',
            'users.group_id',
            'users.schedule_type_id',
            'users.created_at',
            'users.deleted_at'
        ]);
        
        $perPage = $request->input('per_page', 50);
        $page = $request->input('page', 1);
        
        $users = $query->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'data' => UserResource::collection($users->items()),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ]
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Получить информацию о конкретном пользователе
     *
     * @param User $user
     * @return JsonResponse
     */
    public function show(User $user): JsonResponse
    {
        return response()->json(new UserResource($user));
    }

    /**
     * Получить данные пользователя для редактирования
     *
     * @param User $user
     * @return JsonResponse
     */
    public function edit(User $user): JsonResponse
    {
        $user->load(['roles', 'team.roles', 'group', 'scheduleType']);
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'surname' => $user->surname,
            'email' => $user->email,
            'phone' => $user->phone,
            'roles' => $user->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                ];
            }),
            'team_id' => $user->team_id,
            'group_id' => $user->group_id,
            'schedule_type_id' => $user->schedule_type_id,
            'created_at' => $user->created_at,
        ]);
    }

    /**
     * Обновить данные пользователя
     *
     * @param UpdateRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();
        $this->service->update($user, $data);
        
        return response()->json([
            'message' => 'User updated successfully'
        ]);
    }

    /**
     * Удалить пользователя
     *
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(User $user): JsonResponse
    {
        $this->service->destroy($user);
        
        return response()->noContent();
    }

    /**
     * Деактивировать пользователя
     *
     * @param User $user
     * @return JsonResponse
     */
    public function deactivate(User $user): JsonResponse
    {
        $this->service->deactivate($user);
        
        return response()->json([
            'message' => 'User deactivated successfully'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Активировать пользователя
     *
     * @param int $id
     * @return JsonResponse
     */
    public function activate(int $id): JsonResponse
    {
        // Для активации нужно искать в том числе удаленных пользователей
        $user = User::withTrashed()->findOrFail($id);
        $this->service->activate($user);
        
        return response()->json([
            'message' => 'User activated successfully'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Перевести пользователя в другую группу
     * Удаляет все смены после даты перевода и генерирует новые на основе стандартных смен новой группы
     *
     * @param TransferGroupRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function transferGroup(TransferGroupRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();
        $this->service->transferGroup($user, $data['new_group_id'], $data['transfer_date']);
        
        return response()->json([
            'message' => 'Пользователь успешно переведен в новую группу. График обновлен.'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}

