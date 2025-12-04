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
        if (Schema::hasTable('user_shifts')) {
            Schema::table('user_shifts', function (Blueprint $table) {
                if (!Schema::hasColumn('user_shifts', 'deleted_at')) {
                    // Добавляем deleted_at в конец таблицы
                    $table->timestamp('deleted_at')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_shifts', function (Blueprint $table) {
            if (Schema::hasColumn('user_shifts', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }
        });
    }
};
