<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\QualityCriteria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QualityCriterionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = QualityCriteria::with(['teams', 'category'])->where('is_active', true);

        // Фильтрация по команде
        if ($request->has('team_id') && $request->team_id) {
            $query->forTeam($request->team_id);
        }

        // Получение глобальных критериев
        if ($request->has('global_only') && $request->global_only) {
            $query->where('is_global', true);
        }

        // Получение критериев конкретной команды (не глобальных)
        if ($request->has('team_only') && $request->team_only && $request->team_id) {
            $query->where('is_global', false)
                ->whereHas('teams', function($q) use ($request) {
                    $q->where('teams.id', $request->team_id);
                });
        }

        $criteria = $query->orderBy('is_global', 'desc')
            ->orderBy('name')
            ->get();

        return response()->json($criteria);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_score' => 'required|integer|min:1|max:100',
            'team_ids' => 'nullable|array',
            'team_ids.*' => 'exists:teams,id',
            'is_global' => 'boolean',
            'category_id' => 'nullable|integer|exists:quality_criteria_categories,id',
        ]);

        // Если критерий глобальный, игнорируем team_ids
        if ($validated['is_global'] ?? false) {
            $validated['team_ids'] = [];
        }

        $criterion = QualityCriteria::create($validated);

        // Привязываем команды
        if (isset($validated['team_ids']) && !empty($validated['team_ids'])) {
            $criterion->teams()->sync($validated['team_ids']);
        }

        return response()->json($criterion->load(['teams', 'category']), 201);
    }

    public function update(Request $request, QualityCriteria $qualityCriterion): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'max_score' => 'sometimes|integer|min:1|max:100',
            'is_active' => 'sometimes|boolean',
            'team_ids' => 'nullable|array',
            'team_ids.*' => 'exists:teams,id',
            'is_global' => 'sometimes|boolean',
            'category_id' => 'nullable|integer|exists:quality_criteria_categories,id',
        ]);

        // Если критерий становится глобальным, очищаем привязки к командам
        if ($validated['is_global'] ?? $qualityCriterion->is_global) {
            $validated['team_ids'] = [];
        }

        $qualityCriterion->update($validated);

        // Обновляем привязки к командам
        if (array_key_exists('team_ids', $validated)) {
            $qualityCriterion->teams()->sync($validated['team_ids'] ?? []);
        }

        return response()->json($qualityCriterion->load(['teams', 'category']));
    }

    public function destroy(QualityCriteria $qualityCriterion): JsonResponse
    {
        $qualityCriterion->update(['is_active' => false]);
        return response()->json(['message' => 'Критерий деактивирован']);
    }
}
