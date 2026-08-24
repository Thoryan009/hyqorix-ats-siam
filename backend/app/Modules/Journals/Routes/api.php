<?php

use App\Modules\Journals\Controllers\Api\JournalController;
use Illuminate\Support\Facades\Route;

Route::prefix('journals')->group(function () {
    Route::get('/', [JournalController::class, 'index']);
    Route::post('/', [JournalController::class, 'store']);
    Route::get('{journal}', [JournalController::class, 'show']);
});
