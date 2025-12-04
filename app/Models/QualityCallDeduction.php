<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QualityCallDeduction extends Model
{
    protected $fillable = [
        'quality_map_id', 'criteria_id', 'call_id',
        'deduction', 'comment', 'created_by'
    ];

    public function qualityMap()
    {
        return $this->belongsTo(QualityMap::class);
    }

    public function criterion()
    {
        return $this->belongsTo(QualityCriteria::class, 'criteria_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
