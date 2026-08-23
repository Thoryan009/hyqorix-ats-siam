<?php

use App\Modules\Parties\Controllers\Api\PartyController;
use Illuminate\Support\Facades\Route;

Route::prefix('parties')->group(function () {
    Route::get('/', [PartyController::class, 'index']);
    Route::post('/', [PartyController::class, 'store']);
    Route::post('bulk-delete', [PartyController::class, 'bulkDelete']);
    Route::get('{party}', [PartyController::class, 'show']);
    Route::put('{party}', [PartyController::class, 'update']);
    Route::delete('{party}', [PartyController::class, 'destroy']);
});
