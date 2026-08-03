<?php

use Illuminate\Support\Facades\Route;
use App\Modules\__MODEL__\Controllers\Api\__MODEL__Controller;

// Route::apiResource('__TABLE__', __MODEL__Controller::class);
Route::post('auth/login', [__MODEL__Controller::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [__MODEL__Controller::class, 'logout']);
    Route::get('auth/user', [__MODEL__Controller::class, 'user']);
});
