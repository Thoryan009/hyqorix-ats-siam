<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Client\Controllers\Api\ClientController;


Route::crud('clients', ClientController::class, 'client');
Route::get('/client-data', [ClientController::class, 'getClientCountries'])->middleware('permission:client.view');
