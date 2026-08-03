<?php

use App\Modules\Principal\Controllers\Api\PrincipalController;
use Illuminate\Support\Facades\Route;


Route::crud('principals', PrincipalController::class, 'principal');
Route::get('principals-data', [PrincipalController::class, 'getPrincipalData'])->middleware('permission:principal.view');
