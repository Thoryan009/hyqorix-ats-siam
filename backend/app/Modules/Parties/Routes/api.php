<?php

use App\Modules\Parties\Controllers\Api\PartyController;
use App\Modules\Parties\Controllers\Api\PartyTypeController;
use Illuminate\Support\Facades\Route;

Route::prefix('party-types')->group(function () {
    Route::get('/', [PartyTypeController::class, 'index'])->middleware('permission:party_type.view');
    Route::get('options', [PartyTypeController::class, 'options'])->middleware('permission:party_type.view');
    Route::post('/', [PartyTypeController::class, 'store'])->middleware('permission:party_type.view');
    Route::post('bulk-delete', [PartyTypeController::class, 'bulkDelete'])->middleware('permission:party_type.delete');
    Route::get('{partyType}', [PartyTypeController::class, 'show'])->middleware('permission:party_type.view');
    Route::put('{partyType}', [PartyTypeController::class, 'update'])->middleware('permission:party_type.edit');
    Route::delete('{partyType}', [PartyTypeController::class, 'destroy'])->middleware('permission:party_type.delete');
});

Route::prefix('parties')->group(function () {
    Route::get('/', [PartyController::class, 'index'])->middleware('permission:party.view');
    Route::get('source-options/{type}', [PartyController::class, 'sourceOptions'])->middleware('permission:party.view');
    Route::post('/', [PartyController::class, 'store'])->middleware('permission:party.create');
    Route::post('bulk', [PartyController::class, 'bulkStore'])->middleware('permission:party.create');
    Route::post('bulk-delete', [PartyController::class, 'bulkDelete'])->middleware('permission:party.delete');
    Route::get('{party}', [PartyController::class, 'show'])->middleware('permission:party.view');
    Route::put('{party}', [PartyController::class, 'update'])->middleware('permission:party.edit');
    Route::delete('{party}', [PartyController::class, 'destroy'])->middleware('permission:party.delete');
});
