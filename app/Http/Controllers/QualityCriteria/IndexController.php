<?php

namespace App\Http\Controllers\QualityCriteria;

use App\Http\Controllers\User\BaseController;
use App\Models\QualityCriteria;


class IndexController extends BaseController
{

    public function __invoke()
    {
        $qualityCriterias = QualityCriteria::all();
        return response()->json($qualityCriterias);
    }
}
