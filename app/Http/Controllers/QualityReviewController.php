<?php

namespace App\Http\Controllers;

use App\Http\Requests\QualityReview\StoreRequest;
use App\Http\Requests\QualityReview\UpdateRequest;
use App\Http\Resources\QualityReviewResource;
use App\Http\Resources\QualityResource;
use App\Models\QualityReview;
use App\Services\QualityReview\Service;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для работы с обзорами качества
 */
class QualityReviewController extends Controller
{
    /**
     * @var Service Сервис для работы с обзорами качества
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
     * Получить список всех записей качества
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $qualities = $this->service->getAll();
        return response()->json(QualityResource::collection($qualities));
    }

    /**
     * Получить информацию о конкретном обзоре качества
     *
     * @param QualityReview $qualityReview
     * @return JsonResponse
     */
    public function show(QualityReview $qualityReview): JsonResponse
    {
        $qualityReview = $this->service->getWithDeductions($qualityReview);
        return response()->json(new QualityReviewResource($qualityReview));
    }

    /**
     * Создать новый обзор качества
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $qualityReview = $this->service->store($data);
            
            return response()->json([
                'message' => 'Quality review created successfully',
                'data' => new QualityReviewResource($qualityReview)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create quality review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Обновить обзор качества
     *
     * @param UpdateRequest $request
     * @param QualityReview $qualityReview
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, QualityReview $qualityReview): JsonResponse
    {
        try {
            $data = $request->validated();
            $qualityReview = $this->service->update($qualityReview, $data);
            
            return response()->json([
                'message' => 'Quality review updated successfully',
                'data' => new QualityReviewResource($qualityReview)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update quality review',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

