<?php

namespace App\Http\Controllers\Quality;

use App\Http\Controllers\Controller;
use App\Services\Quality\Service;


class BaseController extends Controller
{
    public $service;
    public function __construct(Service $service){
        $this->service = $service;
    }
}
