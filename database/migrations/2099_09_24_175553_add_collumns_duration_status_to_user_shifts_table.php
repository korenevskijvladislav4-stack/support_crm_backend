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
                if (!Schema::hasColumn('user_shifts', 'duration')) {
                    $table->integer('duration')->default(12);
                }
                if (!Schema::hasColumn('user_shifts', 'is_active')) {
                    $table->boolean('is_active')->default(true);
                }
                if (!Schema::hasColumn('user_shifts', 'is_viewed')) {
                    $table->boolean("is_viewed")->default(false);
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
            $table->dropColumn('is_active');
            $table->dropColumn('duration');
            $table->dropColumn('is_viewed');
        });
    }
};
