<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ticket_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->string('filename');
            $table->string('original_name');
            $table->string('mime_type');
            $table->string('path');
            $table->unsignedInteger('size');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ticket_attachments');
    }
};
