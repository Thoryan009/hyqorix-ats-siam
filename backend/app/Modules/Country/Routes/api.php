<?php

use App\Modules\Country\Controllers\Api\CountryController;
use Illuminate\Support\Facades\Route;


Route::crud('countries', CountryController::class, 'country');
