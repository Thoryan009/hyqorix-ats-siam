<?php

namespace App\Modules\__MODEL__\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\__MODEL__\Requests\__REQUEST__;
use App\Modules\__MODEL__\Resources\__RESOURCE__;
use App\Modules\__MODEL__\Services\__SERVICE__;

class __MODEL__Controller extends Controller
{
    public function __construct(
        private readonly __SERVICE__ $service
    ) {}

     public function show($id)
    {
        return new __RESOURCE__($this->service->getById($id));
    }



    public function update(__REQUEST__ $request, $id)
    {

     $record = $this->service->update(
            $id,
            $request->validated()
        );

        return apiSuccess(
            new __RESOURCE__($record),
            'updated'
        );
    }
}
