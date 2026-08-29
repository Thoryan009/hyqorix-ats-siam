<?php

use App\Modules\Journals\Controllers\Api\JournalController;
use App\Modules\Journals\Controllers\Api\JournalTransactionTypeController;
use Illuminate\Support\Facades\Route;

Route::prefix('journal-transaction-types')->group(function () {
    Route::get('/', [JournalTransactionTypeController::class, 'index']);
    Route::get('options', [JournalTransactionTypeController::class, 'options']);
    Route::post('/', [JournalTransactionTypeController::class, 'store']);
    Route::post('bulk-delete', [JournalTransactionTypeController::class, 'bulkDelete']);
    Route::get('{journalTransactionType}', [JournalTransactionTypeController::class, 'show']);
    Route::put('{journalTransactionType}', [JournalTransactionTypeController::class, 'update']);
    Route::delete('{journalTransactionType}', [JournalTransactionTypeController::class, 'destroy']);
});

Route::prefix('journals')->group(function () {
    Route::get('/', [JournalController::class, 'index']);
    Route::get('job-options', [JournalController::class, 'jobOptions']);
    Route::get('demand-letter-options', [JournalController::class, 'demandLetterOptions']);
    Route::post('/', [JournalController::class, 'store']);
    Route::post('{journal}/approve', [JournalController::class, 'approve']);
    Route::post('{journal}/return', [JournalController::class, 'return']);
    Route::post('{journal}/resubmit', [JournalController::class, 'resubmit']);
    Route::post('{journal}/pay', [JournalController::class, 'pay']);
    Route::get('{journal}', [JournalController::class, 'show']);
});
