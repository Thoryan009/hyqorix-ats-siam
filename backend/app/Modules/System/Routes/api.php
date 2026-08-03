<?php

use App\Modules\System\Controllers\Api\SettingController;
use App\Modules\System\Controllers\Api\ActivityLogController;
use Illuminate\Support\Facades\Route;


Route::crud('activity-logs', ActivityLogController::class, 'activity');
