<?php

namespace App\Http\Controllers;

use App\Http\Requests\Quality\StoreCallDeductionRequest;
use App\Models\QualityCallDeduction;
use App\Models\QualityMap;
use Illuminate\Http\JsonResponse;

class QualityCallDeductionController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCallDeductionRequest $request): JsonResponse
    {
        // Ищем существующую запись
        $deduction = QualityCallDeduction::where([
            'quality_map_id' => $request->quality_map_id,
            'criteria_id' => $request->criteria_id,
            'call_id' => $request->call_id,
        ])->first();

        if ($deduction) {
            // Обновляем существующую запись
            $deduction->update([
                'deduction' => $request->deduction,
                'comment' => $request->comment,
                'created_by' => auth()->id(),
            ]);
        } else {
            // Создаем новую запись (на всякий случай)
            $deduction = QualityCallDeduction::create([
                'quality_map_id' => $request->quality_map_id,
                'criteria_id' => $request->criteria_id,
                'call_id' => $request->call_id,
                'deduction' => $request->deduction,
                'comment' => $request->comment,
                'created_by' => auth()->id(),
            ]);
        }

        // Пересчитываем общий балл карты качества
        $qualityMap = QualityMap::find($request->quality_map_id);
        if ($qualityMap) {
            $qualityMap->recalculateTotalScore();
        }

        return response()->json([
            'message' => 'Снятие обновлено',
            'data' => $deduction->load('criterion', 'createdBy')
        ], 200);
    }
}
