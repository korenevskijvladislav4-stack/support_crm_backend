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
        Schema::table('users', function (Blueprint $table) {
            // Индексы для часто используемых полей фильтрации
            if (!$this->hasIndex('users', 'users_name_index')) {
                $table->index('name', 'users_name_index');
            }
            if (!$this->hasIndex('users', 'users_surname_index')) {
                $table->index('surname', 'users_surname_index');
            }
            if (!$this->hasIndex('users', 'users_email_index')) {
                $table->index('email', 'users_email_index');
            }
            if (!$this->hasIndex('users', 'users_team_id_index')) {
                $table->index('team_id', 'users_team_id_index');
            }
            if (!$this->hasIndex('users', 'users_group_id_index')) {
                $table->index('group_id', 'users_group_id_index');
            }
            if (!$this->hasIndex('users', 'users_schedule_type_id_index')) {
                $table->index('schedule_type_id', 'users_schedule_type_id_index');
            }
            // Составной индекс для поиска по имени и фамилии
            if (!$this->hasIndex('users', 'users_name_surname_index')) {
                $table->index(['name', 'surname'], 'users_name_surname_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_name_index');
            $table->dropIndex('users_surname_index');
            $table->dropIndex('users_email_index');
            $table->dropIndex('users_team_id_index');
            $table->dropIndex('users_group_id_index');
            $table->dropIndex('users_schedule_type_id_index');
            $table->dropIndex('users_name_surname_index');
        });
    }

    /**
     * Проверка существования индекса
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $databaseName = $connection->getDatabaseName();
        
        try {
            $result = $connection->select(
                "SELECT COUNT(*) as count 
                 FROM information_schema.statistics 
                 WHERE table_schema = ? 
                 AND table_name = ? 
                 AND index_name = ?",
                [$databaseName, $table, $indexName]
            );
            
            return isset($result[0]) && $result[0]->count > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
};

