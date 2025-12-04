<?php

namespace App\Http\Controllers;

use App\Models\QualityCriteriaCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QualityCriteriaCategoryController extends Controller
{
    /**
     * Получить список всех категорий
     */
    public function index(): JsonResponse
    {
        $categories = QualityCriteriaCategory::orderBy('name')->get();
        return response()->json($categories);
    }

    /**
     * Создать новую категорию
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:quality_criteria_categories,name',
        ]);

        $category = QualityCriteriaCategory::create($validated);

        return response()->json($category, 201);
    }

    /**
     * Получить конкретную категорию
     */
    public function show(QualityCriteriaCategory $qualityCriteriaCategory): JsonResponse
    {
        return response()->json($qualityCriteriaCategory);
    }

    /**
     * Обновить категорию
     */
    public function update(Request $request, QualityCriteriaCategory $qualityCriteriaCategory): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:quality_criteria_categories,name,' . $qualityCriteriaCategory->id,
        ]);

        $qualityCriteriaCategory->update($validated);

        return response()->json($qualityCriteriaCategory);
    }

    /**
     * Удалить категорию
     */
    public function destroy(QualityCriteriaCategory $qualityCriteriaCategory): JsonResponse
    {
        // Проверяем, есть ли критерии с этой категорией
        if ($qualityCriteriaCategory->criteria()->count() > 0) {
            return response()->json([
                'message' => 'Невозможно удалить категорию, так как она используется в критериях качества'
            ], 422);
        }

        $qualityCriteriaCategory->delete();

        return response()->json(['message' => 'Категория успешно удалена']);
    }
}

