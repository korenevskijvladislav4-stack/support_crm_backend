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
        if (Schema::hasTable('attempts')) {
            Schema::table('attempts', function (Blueprint $table) {
                if (!Schema::hasColumn('attempts', 'phone')) {
                    $table->string('phone', 20)->nullable()->after('email')->comment('Номер телефона');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('attempts')) {
            Schema::table('attempts', function (Blueprint $table) {
                if (Schema::hasColumn('attempts', 'phone')) {
                    $table->dropColumn('phone');
                }
            });
        }
    }
};
