<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('user_shifts') && !Schema::hasColumn('user_shifts', 'id')) {
            // Добавляем колонку id без автоинкремента сначала
            Schema::table('user_shifts', function (Blueprint $table) {
                $table->unsignedBigInteger('id')->first();
            });
            
            // Заполняем id для существующих записей
            DB::statement('SET @row_number = 0');
            DB::statement('UPDATE user_shifts SET id = (@row_number:=@row_number+1)');
            
            // Делаем id первичным ключом и автоинкрементом
            Schema::table('user_shifts', function (Blueprint $table) {
                $table->primary('id');
            });
            
            // Устанавливаем AUTO_INCREMENT
            DB::statement('ALTER TABLE user_shifts MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_shifts') && Schema::hasColumn('user_shifts', 'id')) {
            Schema::table('user_shifts', function (Blueprint $table) {
                $table->dropPrimary();
                $table->dropColumn('id');
            });
        }
    }
};
