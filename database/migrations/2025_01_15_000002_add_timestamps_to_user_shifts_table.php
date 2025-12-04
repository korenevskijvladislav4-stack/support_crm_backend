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
                // Добавляем timestamps для отслеживания времени создания и обновления
                if (!Schema::hasColumn('user_shifts', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }
                if (!Schema::hasColumn('user_shifts', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
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
            if (Schema::hasColumn('user_shifts', 'created_at')) {
                $table->dropColumn('created_at');
            }
            if (Schema::hasColumn('user_shifts', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
    }
};

