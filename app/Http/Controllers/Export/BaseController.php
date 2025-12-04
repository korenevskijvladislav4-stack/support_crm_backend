<?php

namespace App\Http\Controllers\Export;

use App\Http\Controllers\Controller;
use App\Services\Export\Service;


class BaseController extends Controller
{
    public $service;
    public function __construct(Service $service){
        $this->service = $service;
    }
}
