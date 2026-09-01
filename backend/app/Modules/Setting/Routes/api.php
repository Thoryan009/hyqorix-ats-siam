<?php

use App\Modules\Setting\Controllers\Api\SettingController;
use Illuminate\Support\Facades\Route;

Route::post('settings/backup', [SettingController::class, 'backup']);

Route::get('settings/party-type-mappings', [SettingController::class, 'partyTypeMappingsShow']);
Route::put('settings/party-type-mappings', [SettingController::class, 'partyTypeMappingsUpdate']);

Route::get('settings/embassy/{setting}', [SettingController::class, 'embassyShow']);
Route::put('settings/embassy/{setting}', [SettingController::class, 'embassyUpdate']);

Route::get('setting-data', [SettingController::class, 'getSettingData']);

Route::get('settings/{setting}', [SettingController::class, 'show']);
Route::put('settings/{setting}', [SettingController::class, 'update']);
