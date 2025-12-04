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
        Schema::create('team_roles', function (Blueprint $table) {
            $table->foreignId("team_id")->constrained("teams")->onDelete("cascade");
            $table->foreignId("role_id")->constrained("roles")->onDelete("cascade");
            $table->unsignedTinyInteger("salary")->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_roles');
    }
};
