<?php

namespace App\Http\Controllers\QualityCriteria;

use App\Http\Requests\QualityCriteria\StoreRequest;


class StoreController extends BaseController
{

    public function __invoke(StoreRequest $request)
    {
        $data = $request->validated();
        $this->service->store($data);
        return response()->noContent();
    }

}
