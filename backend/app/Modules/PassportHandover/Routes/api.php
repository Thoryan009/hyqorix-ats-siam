<?php

use App\Modules\PassportHandover\Controllers\Api\PassportHandoverController;
use Illuminate\Support\Facades\Route;

Route::get('passport-handovers/search-by-passport', [PassportHandoverController::class, 'searchByPassport'])
    ->middleware('permission:passport_handover.view');

Route::crud('passport-handovers', PassportHandoverController::class, 'passport_handover');

Route::post('passport-handovers/{passportHandover}/collect', [PassportHandoverController::class, 'collect'])
    ->middleware('permission:passport_handover.collect');
