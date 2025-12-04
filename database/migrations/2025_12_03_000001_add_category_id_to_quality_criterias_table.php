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
        Schema::table('quality_criterias', function (Blueprint $table) {
            if (!Schema::hasColumn('quality_criterias', 'category_id')) {
                $table->foreignId('category_id')
                    ->nullable()
                    ->after('is_global')
                    ->constrained('quality_criteria_categories')
                    ->onDelete('set null')
                    ->comment('Категория критерия качества');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quality_criterias', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['category_id']);
        });
    }
};

