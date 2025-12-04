<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->string('title');
            $table->text('description');
            $table->foreignId('type_id')->constrained('ticket_types');
            $table->foreignId('status_id')->constrained('ticket_statuses');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->foreignId('creator_id')->constrained('users');
            $table->foreignId('team_id')->nullable()->constrained('teams');
            $table->json('custom_fields')->nullable(); // Динамические поля из типа тикета
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tickets');
    }
};
