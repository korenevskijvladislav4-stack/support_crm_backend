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
        if (!Schema::hasTable('quality_call_deductions')) {
            Schema::create('quality_call_deductions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('quality_map_id')->constrained();
                $table->foreignId('criteria_id')->constrained('quality_criterias');
                $table->string('call_id');
                $table->integer('deduction')->default(0)->comment('Размер снятия 0-100');
                $table->text('comment')->nullable();
                $table->foreignId('created_by')->constrained('users');
                $table->timestamps();

                // Уникальность комбинации карта-критерий-звонок
                $table->unique(['quality_map_id', 'criteria_id', 'call_id'], 'qcd_map_criteria_call_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_call_deductions');
    }
};
