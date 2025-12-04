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
                if (!Schema::hasColumn('user_shifts', 'status')) {
                    // Статусы: 'approved' (одобрена), 'pending' (ожидает), 'rejected' (отклонена)
                    // По умолчанию 'approved' для существующих записей
                    if (Schema::hasColumn('user_shifts', 'is_viewed')) {
                        $table->string('status')->default('approved')->after('is_viewed');
                    } else {
                        $table->string('status')->default('approved');
                    }
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
            $table->dropColumn('status');
        });
    }
};

