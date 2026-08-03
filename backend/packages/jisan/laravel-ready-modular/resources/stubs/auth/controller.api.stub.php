<?php

namespace App\Modules\__MODEL__\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\__MODEL__\Requests\__REQUEST__;
use App\Modules\__MODEL__\Services\__SERVICE__;
use App\Modules\__MODEL__\Resources\__RESOURCE__;

class __MODEL__Controller extends Controller
{
    public function __construct(
        private readonly __SERVICE__ $service
    ) {}

     public function login(__REQUEST__ $request)
    {
        $loginData = $this->service->login($request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'role' => $loginData['user']->type,
            'token' => $loginData['token'],
        ]);
    }


    public function logout()
    {
        $this->service->logout();
        return response()->json(['success' => true, 'message' => 'Logged out successfully']);
    }

    public function user()
    {
        return new __RESOURCE__($this->service->getUser());
    }
}
