<?php

namespace App\Http\Controllers\ExtraShift;

use App\Http\Controllers\Controller;
use App\Services\ExtraShift\Service;


class BaseController extends Controller
{
    public $service;
    public function __construct(Service $service){
        $this->service = $service;
    }
}
