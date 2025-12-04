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
        Schema::create('quality_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quality_map_id')->constrained();
            $table->foreignId('criteria_id')->constrained('quality_criterias');
            $table->string('chat_id');
            $table->integer('deduction')->default(0)->comment('Размер снятия 0-100');
            $table->text('comment')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            // Уникальность комбинации карта-критерий-чат
            $table->unique(['quality_map_id', 'criteria_id', 'chat_id'], 'qd_map_criteria_chat_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_deductions');
    }
};
