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
        Schema::create('penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->comment('Пользователь-нарушитель');
            $table->foreignId('created_by')->constrained('users')->comment('Кто внес нарушение');
            $table->integer('hours_to_deduct')->comment('Количество рабочих часов для снятия');
            $table->text('comment')->comment('Комментарий (объяснение нарушения)');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->comment('Статус: ожидает/одобрен/отклонен');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penalties');
    }
};
