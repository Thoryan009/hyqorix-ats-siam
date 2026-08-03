<?php

use Illuminate\Support\Facades\Route;
use App\Modules\__MODEL__\Controllers\Api\__MODEL__Controller;


Route::get('__TABLE__/{__ROUTE_PARAM__}', [__MODEL__Controller::class, 'show']);
Route::put('__TABLE__/{__ROUTE_PARAM__}', [__MODEL__Controller::class, 'update']);
