<?php

namespace App\Services\QualityReview;

use App\Models\Quality;
use App\Models\QualityReview;
use Illuminate\Support\Facades\DB;

/**
 * Сервис для работы с обзорами качества
 */
class Service
{
    /**
     * Создать новый обзор качества
     *
     * @param array $data Данные обзора
     * @return QualityReview Созданный обзор
     * @throws \Exception
     */
    public function store(array $data): QualityReview
    {
        $quality = Quality::find($data['quality_id']);
        if (!$quality) {
            throw new \Exception('Quality not found');
        }

        $review = $quality->reviews()->create([
            'review' => $data['review'] ?? null,
            'review_type' => $data['review_type'] ?? null,
        ]);

        $criterias = $quality->user->team->criterias ?? collect();
        foreach ($criterias as $criteria) {
            $review->deductions()->create([
                'quality_review_id' => $review->id,
                'quality_criteria_id' => $criteria->id,
            ]);
        }

        return $review->load('deductions');
    }

    /**
     * Обновить обзор качества
     *
     * @param QualityReview $qualityReview
     * @param array $data Данные для обновления
     * @return QualityReview Обновленный обзор
     * @throws \Exception
     */
    public function update(QualityReview $qualityReview, array $data): QualityReview
    {
        DB::beginTransaction();

        try {
            // Обновляем основную модель
            $qualityReview->update([
                'review' => $data['review'] ?? $qualityReview->review,
                'review_type' => $data['review_type'] ?? $qualityReview->review_type
            ]);

            // Обрабатываем deductions
            if (isset($data['deductions'])) {
                foreach ($data['deductions'] as $deductionData) {
                    $qualityReview->deductions()
                        ->where('id', $deductionData['id'])
                        ->update([
                            'points' => $deductionData['points'],
                            'comments' => $deductionData['comments'] ?? null
                        ]);
                }
            }

            // Пересчитываем общий балл
            $totalDeductions = $qualityReview->deductions()->sum('points');
            $score = max(0, 100 - $totalDeductions);
            $qualityReview->update(['total_score' => $score]);

            // Обновляем среднее качество
            if ($qualityReview->quality) {
                $averageQuality = $qualityReview->quality->reviews()->avg('total_score');
                $qualityReview->quality->update(['average_quality' => $averageQuality]);
            }

            DB::commit();

            return $qualityReview->fresh(['deductions', 'quality']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Получить список всех обзоров качества
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return Quality::all();
    }

    /**
     * Получить обзор качества с вычетами
     *
     * @param QualityReview $qualityReview
     * @return QualityReview
     */
    public function getWithDeductions(QualityReview $qualityReview): QualityReview
    {
        return $qualityReview->load('deductions');
    }
}

