<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\QualityMap;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Поле total_score теперь создается в базовой миграции create_quaity_maps_table
        // Оставляем для обратной совместимости и пересчета существующих данных
        Schema::table('quality_maps', function (Blueprint $table) {
            if (!Schema::hasColumn('quality_maps', 'total_score')) {
                $table->integer('total_score')->default(0)->after('calls_count')->comment('Общий балл карты качества (пересчитывается автоматически)');
            }
        });

        // Пересчитываем total_score для всех существующих карт качества
        if (Schema::hasTable('quality_maps')) {
            try {
                QualityMap::chunk(100, function ($qualityMaps) {
                    foreach ($qualityMaps as $qualityMap) {
                        try {
                            $qualityMap->recalculateTotalScore();
                        } catch (\Exception $e) {
                            // Игнорируем ошибки при пересчете отдельных записей
                            \Log::warning("Failed to recalculate total_score for quality_map {$qualityMap->id}: " . $e->getMessage());
                        }
                    }
                });
            } catch (\Exception $e) {
                // Игнорируем ошибки при пересчете, если таблица пуста или есть проблемы
                \Log::warning("Failed to recalculate total_score for quality_maps: " . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quality_maps', function (Blueprint $table) {
            $table->dropColumn('total_score');
        });
    }
};
