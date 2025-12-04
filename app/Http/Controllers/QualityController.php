<?php

namespace App\Http\Controllers;

use App\Http\Resources\QualityResource;
use App\Models\Quality;
use App\Services\Quality\Service;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для работы с качеством
 */
class QualityController extends Controller
{
    /**
     * @var Service Сервис для работы с качеством
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
     * Получить информацию о конкретной записи качества с обзорами
     *
     * @param Quality $quality
     * @return JsonResponse
     */
    public function show(Quality $quality): JsonResponse
    {
        $quality = $this->service->getWithReviews($quality);
        return response()->json(new QualityResource($quality));
    }
}
