<?php

namespace App\Http\Controllers\QualityCriteria;

use App\Http\Controllers\Controller;
use App\Services\QualityCriteria\Service;


class BaseController extends Controller
{
    public $service;
    public function __construct(Service $service){
        $this->service = $service;
    }
}
