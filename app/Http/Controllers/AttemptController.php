<?php

namespace App\Http\Controllers;

use App\Http\Filters\AttemptFilter;
use App\Http\Requests\Attempt\ApproveRequest;
use App\Http\Requests\Attempt\FilterRequest;
use App\Http\Resources\AttemptsResource;
use App\Models\Attempt;
use App\Services\Attempt\Service;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для работы с попытками регистрации
 */
class AttemptController extends Controller
{
    /**
     * @var Service Сервис для работы с попытками
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
     * Получить список попыток регистрации
     *
     * @param FilterRequest $request
     * @return JsonResponse
     */
    public function index(FilterRequest $request): JsonResponse
    {
        $data = $request->validated();
        // Фильтруем только null и пустые строки, но оставляем false, 0 и пустые массивы
        $filteredData = array_filter($data, function ($value) {
            return $value !== null && $value !== '' && (!is_array($value) || count($value) > 0);
        }, ARRAY_FILTER_USE_BOTH);
        $filter = app()->make(AttemptFilter::class, ['queryParams' => $filteredData]);
        
        $query = Attempt::query();
        
        // Применяем фильтры
        $query->filter($filter);

        // Сортировка
        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->input('per_page', 50);
        $page = $request->input('page', 1);
        
        $attempts = $query->paginate($perPage, ['*'], 'page', $page);

        $resourceCollection = AttemptsResource::collection($attempts->items());
        
        return response()->json([
            'data' => $resourceCollection->resolve(),
            'meta' => [
                'current_page' => $attempts->currentPage(),
                'last_page' => $attempts->lastPage(),
                'per_page' => $attempts->perPage(),
                'total' => $attempts->total(),
                'from' => $attempts->firstItem(),
                'to' => $attempts->lastItem(),
            ]
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Получить информацию о конкретной попытке
     *
     * @param Attempt $attempt
     * @return JsonResponse
     */
    public function show(Attempt $attempt): JsonResponse
    {
        return response()->json(new AttemptsResource($attempt));
    }

    /**
     * Одобрить попытку регистрации и создать пользователя
     *
     * @param ApproveRequest $request
     * @param Attempt $attempt
     * @return JsonResponse
     */
    public function approve(ApproveRequest $request, Attempt $attempt): JsonResponse
    {
        $data = $request->validated();
        $this->service->approve($data, $attempt);
        
        return response()->json([
            'message' => 'Attempt approved and user created successfully'
        ]);
    }

    /**
     * Удалить попытку регистрации
     *
     * @param Attempt $attempt
     * @return JsonResponse
     */
    public function destroy(Attempt $attempt): JsonResponse
    {
        $this->service->destroy($attempt);
        
        return response()->json([
            'message' => 'Attempt deleted successfully'
        ]);
    }
}
