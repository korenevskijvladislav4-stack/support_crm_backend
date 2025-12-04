<?php

namespace App\Http\Controllers;

use App\Http\Filters\PenaltyFilter;
use App\Http\Requests\Penalty\FilterRequest;
use App\Http\Requests\Penalty\StoreRequest;
use App\Http\Requests\Penalty\UpdateRequest;
use App\Http\Resources\PenaltyResource;
use App\Models\Penalty;
use App\Services\Penalty\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PenaltyController extends Controller
{
    protected Service $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * Получить список штрафов
     *
     * @param FilterRequest $request
     * @return JsonResponse
     */
    public function index(FilterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $filter = app()->make(PenaltyFilter::class, ['queryParams' => array_filter($data)]);
        
        $query = Penalty::with(['user', 'creator']);
        
        // Применяем фильтры
        $query->filter($filter);

        // Фильтрация по статусу
        $status = $request->input('status', 'all');
        if (!empty($status) && $status !== 'all') {
            $query->where('status', $status);
        }

        // Сортировка
        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->input('per_page', 50);
        $page = $request->input('page', 1);
        
        $penalties = $query->paginate($perPage, ['*'], 'page', $page);

        $resourceCollection = PenaltyResource::collection($penalties->items());
        
        return response()->json([
            'data' => $resourceCollection->resolve(),
            'meta' => [
                'current_page' => $penalties->currentPage(),
                'last_page' => $penalties->lastPage(),
                'per_page' => $penalties->perPage(),
                'total' => $penalties->total(),
                'from' => $penalties->firstItem(),
                'to' => $penalties->lastItem(),
            ]
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Получить конкретный штраф
     *
     * @param Penalty $penalty
     * @return JsonResponse
     */
    public function show(Penalty $penalty): JsonResponse
    {
        $penalty->load(['user', 'creator']);
        
        return response()->json(new PenaltyResource($penalty), 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Создать штраф
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $penalty = $this->service->store($data, $request->user()->id);
        $penalty->load(['user', 'creator']);

        return response()->json(new PenaltyResource($penalty), 201, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Обновить штраф
     *
     * @param UpdateRequest $request
     * @param Penalty $penalty
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, Penalty $penalty): JsonResponse
    {
        $data = $request->validated();
        $penalty = $this->service->update($penalty, $data);
        $penalty->load(['user', 'creator']);

        return response()->json(new PenaltyResource($penalty), 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Одобрить штраф
     *
     * @param Penalty $penalty
     * @return JsonResponse
     */
    public function approve(Penalty $penalty): JsonResponse
    {
        $penalty = $this->service->approve($penalty);
        $penalty->load(['user', 'creator']);

        return response()->json(new PenaltyResource($penalty), 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Отклонить штраф
     *
     * @param Penalty $penalty
     * @return JsonResponse
     */
    public function reject(Penalty $penalty): JsonResponse
    {
        $penalty = $this->service->reject($penalty);
        $penalty->load(['user', 'creator']);

        return response()->json(new PenaltyResource($penalty), 200, [], JSON_UNESCAPED_UNICODE);
    }
}
