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
        if (Schema::hasTable('user_shifts')) {
            Schema::table('user_shifts', function (Blueprint $table) {
                if (!Schema::hasColumn('user_shifts', 'is_requested')) {
                    $table->boolean('is_requested')->default(false)->after('status');
                }
            });

            // Обновляем существующие записи: все существующие смены считаются стандартными
            DB::table('user_shifts')
                ->whereNull('is_requested')
                ->update(['is_requested' => false]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_shifts', function (Blueprint $table) {
            if (Schema::hasColumn('user_shifts', 'is_requested')) {
                $table->dropColumn('is_requested');
            }
        });
    }
};
