<?php

namespace App\Http\Controllers\Schedule;

use App\Http\Controllers\Controller;
use App\Services\Schedule\Service;


class BaseController extends Controller
{
    public $service;
    public function __construct(Service $service){
        $this->service = $service;
    }
}
