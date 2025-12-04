<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QualityCriteriaCategory extends Model
{
    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Получить все критерии этой категории
     */
    public function criteria(): HasMany
    {
        return $this->hasMany(QualityCriteria::class, 'category_id');
    }
}

