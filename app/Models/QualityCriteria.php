<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QualityCriteria extends Model
{
    protected $fillable = [
        'name',
        'description',
        'max_score',
        'is_active',
        'is_global',
        'category_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_score' => 'integer',
        'is_global' => 'boolean'
    ];

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'criteria_team', 'criteria_id', 'team_id');
    }

    /**
     * Получить категорию критерия
     */
    public function category()
    {
        return $this->belongsTo(QualityCriteriaCategory::class, 'category_id');
    }

    // Scope для получения критериев команды
    // app/Models/QualityCriterion.php
    public function scopeForTeam($query, $teamId)
    {
        return $query->where(function($q) use ($teamId) {
            $q->where('is_global', true)
                ->orWhereHas('teams', function($query) use ($teamId) {
                    $query->where('teams.id', $teamId);
                });
        });
    }

}
