<?php

namespace App\Http\Controllers\QualityCriteria;

use App\Http\Controllers\Schedule\BaseController;
use App\Models\Team;

class CreateController extends BaseController
{

    public function __invoke()
    {
        $teams = Team::all();
        return view('quality_criterias.create', compact('teams'));
    }

}
