<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Эти поля теперь создаются в базовой миграции create_quaity_maps_table
        // Оставляем для обратной совместимости
        Schema::table('quality_maps', function (Blueprint $table) {
            if (!Schema::hasColumn('quality_maps', 'call_ids')) {
                $table->json('call_ids')->nullable()->after('chat_ids')->comment('Массив ID звонков для столбцов');
            }
            if (!Schema::hasColumn('quality_maps', 'calls_count')) {
                $table->integer('calls_count')->default(0)->after('call_ids')->comment('Количество проверяемых звонков');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quality_maps', function (Blueprint $table) {
            $table->dropColumn(['call_ids', 'calls_count']);
        });
    }
};
