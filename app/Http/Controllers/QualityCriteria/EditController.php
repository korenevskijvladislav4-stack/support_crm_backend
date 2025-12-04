<?php

namespace App\Http\Controllers\QualityCriteria;

use App\Http\Controllers\User\BaseController;


class EditController extends BaseController
{
    public function __invoke($id)
    {
        return view('qualities.edit', compact('id'));
    }
}
