<?php

use App\Modules\Parties\Controllers\Api\PartyController;
use App\Modules\Parties\Controllers\Api\PartyTypeController;
use Illuminate\Support\Facades\Route;

Route::prefix('party-types')->group(function () {
    Route::get('/', [PartyTypeController::class, 'index']);
    Route::get('options', [PartyTypeController::class, 'options']);
    Route::post('/', [PartyTypeController::class, 'store']);
    Route::post('bulk-delete', [PartyTypeController::class, 'bulkDelete']);
    Route::get('{partyType}', [PartyTypeController::class, 'show']);
    Route::put('{partyType}', [PartyTypeController::class, 'update']);
    Route::delete('{partyType}', [PartyTypeController::class, 'destroy']);
});

Route::prefix('parties')->group(function () {
    Route::get('/', [PartyController::class, 'index']);
    Route::get('source-options/{type}', [PartyController::class, 'sourceOptions']);
    Route::post('/', [PartyController::class, 'store']);
    Route::post('bulk', [PartyController::class, 'bulkStore']);
    Route::post('bulk-delete', [PartyController::class, 'bulkDelete']);
    Route::get('{party}', [PartyController::class, 'show']);
    Route::put('{party}', [PartyController::class, 'update']);
    Route::delete('{party}', [PartyController::class, 'destroy']);
});
