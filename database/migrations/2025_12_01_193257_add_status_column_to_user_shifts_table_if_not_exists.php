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
        if (Schema::hasTable('user_shifts') && !Schema::hasColumn('user_shifts', 'status')) {
            Schema::table('user_shifts', function (Blueprint $table) {
                // Добавляем колонку status после is_viewed, если она существует, иначе просто в конец
                if (Schema::hasColumn('user_shifts', 'is_viewed')) {
                    $table->string('status')->default('approved')->after('is_viewed');
                } else {
                    $table->string('status')->default('approved');
                }
            });
            
            // Обновляем существующие записи, устанавливая статус 'approved'
            DB::table('user_shifts')->whereNull('status')->update(['status' => 'approved']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_shifts') && Schema::hasColumn('user_shifts', 'status')) {
            Schema::table('user_shifts', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
