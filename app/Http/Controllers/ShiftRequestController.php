<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShiftRequest\StoreRequest;
use App\Http\Requests\ShiftRequest\UpdateRequest;
use App\Http\Requests\ShiftRequest\CreateDirectRequest;
use App\Http\Resources\UserShiftResource;
use App\Models\UserShift;
use App\Services\ShiftRequest\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Контроллер для работы с запросами дополнительных смен
 */
class ShiftRequestController extends Controller
{
    /**
     * @var Service Сервис для работы с запросами смен
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
     * Получить список запрошенных смен текущего пользователя
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $requests = $this->service->getUserRequests();
        return response()->json(UserShiftResource::collection($requests));
    }

    /**
     * Создать запрос на дополнительную смену
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $userShift = $this->service->requestShift($data);
            
            return response()->json([
                'message' => 'Запрос на дополнительную смену создан',
                'data' => new UserShiftResource($userShift->load(['shift', 'user']))
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ошибка при создании запроса',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Одобрить запрос на смену
     *
     * @param int $id ID записи user_shifts
     * @return JsonResponse
     */
    public function approve(int $id): JsonResponse
    {
        try {
            // Ищем запись напрямую по ID
            $userShift = UserShift::find($id);
            
            if (!$userShift) {
                return response()->json([
                    'message' => 'Смена не найдена',
                    'error' => "Запись с ID {$id} не существует в базе данных"
                ], 404);
            }
            
            if ($userShift->status !== UserShift::STATUS_PENDING) {
                return response()->json([
                    'message' => 'Можно одобрить только запросы со статусом pending'
                ], 400);
            }

            $userShift = $this->service->approve($userShift);
            
            return response()->json([
                'message' => 'Запрос на смену одобрен',
                'data' => new UserShiftResource($userShift->load(['shift', 'user']))
            ]);
        } catch (\Exception $e) {
            Log::error('Error approving UserShift', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'message' => 'Ошибка при одобрении смены',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Отклонить запрос на смену
     *
     * @param int $id ID записи user_shifts
     * @return JsonResponse
     */
    public function reject(int $id): JsonResponse
    {
        try {
            // Ищем запись напрямую по ID
            $userShift = UserShift::find($id);
            
            if (!$userShift) {
                return response()->json([
                    'message' => 'Смена не найдена',
                    'error' => "Запись с ID {$id} не существует в базе данных"
                ], 404);
            }
            
            if ($userShift->status !== UserShift::STATUS_PENDING) {
                return response()->json([
                    'message' => 'Можно отклонить только запросы со статусом pending'
                ], 400);
            }

            $this->service->reject($userShift);
            
            return response()->json([
                'message' => 'Запрос на смену отклонен'
            ]);
        } catch (\Exception $e) {
            Log::error('Error rejecting UserShift', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'message' => 'Ошибка при отклонении смены',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Редактировать смену (изменить продолжительность)
     *
     * @param UpdateRequest $request
     * @param int $id ID записи user_shifts
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        try {
            // Ищем запись напрямую по ID
            $userShift = UserShift::find($id);
            
            if (!$userShift) {
                Log::warning('UserShift not found for update', ['id' => $id]);
                return response()->json([
                    'message' => 'Смена не найдена',
                    'error' => "Запись с ID {$id} не существует в базе данных"
                ], 404);
            }
            
            $data = $request->validated();
            
            Log::info('Updating UserShift', [
                'id' => $userShift->id,
                'current_duration' => $userShift->duration,
                'new_duration' => $data['duration'],
            ]);
            
            $userShift = $this->service->update($userShift, $data['duration']);
            
            return response()->json([
                'message' => 'Смена успешно отредактирована',
                'data' => new UserShiftResource($userShift->load(['shift', 'user']))
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating UserShift', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Ошибка при редактировании смены',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Удалить смену
     *
     * @param int $id ID записи user_shifts
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            // Ищем запись напрямую по ID
            $userShift = UserShift::find($id);
            
            if (!$userShift) {
                Log::warning('UserShift not found', ['id' => $id]);
                return response()->json([
                    'message' => 'Смена не найдена',
                    'error' => "Запись с ID {$id} не существует в базе данных"
                ], 404);
            }
            
            // Логируем информацию о смене перед удалением
            Log::info('Deleting UserShift', [
                'id' => $userShift->id,
                'user_id' => $userShift->user_id,
                'shift_id' => $userShift->shift_id,
                'status' => $userShift->status,
            ]);
            
            $result = $this->service->destroy($userShift);
            
            if (!$result) {
                return response()->json([
                    'message' => 'Не удалось удалить смену',
                    'error' => 'Операция удаления вернула false'
                ], 400);
            }
            
            return response()->json([
                'message' => 'Смена успешно удалена'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting UserShift', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Ошибка при удалении смены',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Создать смену напрямую без запроса (одобрена сразу)
     *
     * @param CreateDirectRequest $request
     * @return JsonResponse
     */
    public function createDirect(CreateDirectRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $userShift = $this->service->createDirect($data);
            
            return response()->json([
                'message' => 'Смена успешно добавлена',
                'data' => new UserShiftResource($userShift->load(['shift', 'user']))
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ошибка при создании смены',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}

