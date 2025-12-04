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
        Schema::create('quality_maps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('checker_id')->constrained('users');
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('team_id')->constrained();
            $table->json('chat_ids')->comment('Массив ID чатов для столбцов');
            $table->json('call_ids')->nullable()->comment('Массив ID звонков для столбцов');
            $table->integer('calls_count')->default(0)->comment('Количество проверяемых звонков');
            $table->integer('total_score')->default(0)->comment('Общий балл карты качества (пересчитывается автоматически)');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_maps');
    }
};
