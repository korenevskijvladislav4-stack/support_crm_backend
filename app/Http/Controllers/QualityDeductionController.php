<?php

namespace App\Http\Controllers;

use App\Http\Requests\Quality\StoreDeductionRequest;
use App\Models\QualityDeduction;
use App\Models\QualityMap;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QualityDeductionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDeductionRequest $request): JsonResponse
    {
        // Ищем существующую запись
        $deduction = QualityDeduction::where([
            'quality_map_id' => $request->quality_map_id,
            'criteria_id' => $request->criteria_id,
            'chat_id' => $request->chat_id,
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
            $deduction = QualityDeduction::create([
                'quality_map_id' => $request->quality_map_id,
                'criteria_id' => $request->criteria_id,
                'chat_id' => $request->chat_id,
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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreDeductionRequest $request, QualityDeduction $qualityDeduction): JsonResponse
    {
        $qualityDeduction->update([
            'deduction' => $request->deduction,
            'comment' => $request->comment,
            'created_by' => auth()->id(),
        ]);

        // Пересчитываем общий балл карты качества
        $qualityMap = QualityMap::find($qualityDeduction->quality_map_id);
        if ($qualityMap) {
            $qualityMap->recalculateTotalScore();
        }

        return response()->json([
            'message' => 'Снятие обновлено',
            'data' => $qualityDeduction->load('criterion', 'createdBy')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
