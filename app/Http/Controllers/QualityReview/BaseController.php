<?php

namespace App\Http\Controllers\QualityReview;

use App\Http\Controllers\Controller;
use App\Services\QualityReview\Service;


class BaseController extends Controller
{
    public $service;
    public function __construct(Service $service){
        $this->service = $service;
    }
}
