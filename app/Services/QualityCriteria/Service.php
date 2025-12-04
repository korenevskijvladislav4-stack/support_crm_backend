<?php

namespace App\Services\QualityCriteria;

use App\Models\QualityCriteria;

class Service
{
    public function store($data){
        $teams = $data['teams_ids'];
        unset($data['teams_ids']);
        $qualityCriteria = QualityCriteria::create($data);
        $qualityCriteria->teams()->attach($teams);
        return $qualityCriteria;
    }
}
