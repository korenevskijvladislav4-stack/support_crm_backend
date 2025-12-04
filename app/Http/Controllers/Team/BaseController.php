<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use App\Services\Team\Service;


class BaseController extends Controller
{
    public $service;
    public function __construct(Service $service){
        $this->service = $service;
    }
}
