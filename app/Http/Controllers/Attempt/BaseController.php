<?php

namespace App\Http\Controllers\Attempt;

use App\Http\Controllers\Controller;
use App\Services\Attempt\Service;


class BaseController extends Controller
{
    public $service;
    public function __construct(Service $service){
        $this->service = $service;
    }
}
