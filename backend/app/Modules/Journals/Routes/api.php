<?php

use App\Modules\Journals\Controllers\Api\GeneralLedgerController;
use App\Modules\Journals\Controllers\Api\JournalController;
use App\Modules\Journals\Controllers\Api\PartyLedgerController;
use App\Modules\Journals\Controllers\Api\JournalTransactionTypeController;
use Illuminate\Support\Facades\Route;

Route::prefix('journal-transaction-types')->group(function () {
    Route::get('/', [JournalTransactionTypeController::class, 'index'])->middleware('permission:transaction_type.view');
    Route::get('options', [JournalTransactionTypeController::class, 'options'])->middleware('permission:transaction_type.view');
    Route::post('/', [JournalTransactionTypeController::class, 'store'])->middleware('permission:transaction_type.create');
    Route::post('bulk-delete', [JournalTransactionTypeController::class, 'bulkDelete'])->middleware('permission:transaction_type.delete');
    Route::get('{journalTransactionType}', [JournalTransactionTypeController::class, 'show'])->middleware('permission:transaction_type.view');
    Route::put('{journalTransactionType}', [JournalTransactionTypeController::class, 'update'])->middleware('permission:transaction_type.edit');
    Route::delete('{journalTransactionType}', [JournalTransactionTypeController::class, 'destroy'])->middleware('permission:transaction_type.delete');
});

Route::prefix('journals')->group(function () {
    Route::get('/', [JournalController::class, 'index'])->middleware('permission:journal.view')->middleware('permission:journal.view');
    Route::get('party-ledger', [PartyLedgerController::class, 'index'])->middleware('permission:ledger.party');
    Route::get('party-ledger/export', [PartyLedgerController::class, 'export'])->middleware('permission:ledger.party');
    Route::get('general-ledger', [GeneralLedgerController::class, 'index'])->middleware('permission:ledger.general');
    Route::get('general-ledger/export', [GeneralLedgerController::class, 'export'])->middleware('permission:ledger.general');
    Route::get('job-options', [JournalController::class, 'jobOptions'])->middleware('permission:journal.bill_entry');
    Route::get('demand-letter-options', [JournalController::class, 'demandLetterOptions'])->middleware('permission:journal.bill_entry');
    Route::get('sub-ledger-applicants', [JournalController::class, 'subLedgerApplicants'])->middleware('permission:journal.bill_entry');
    Route::post('narration-hints', [JournalController::class, 'narrationHints'])->middleware('permission:journal.bill_entry');
    Route::post('entry-chat', [JournalController::class, 'entryChat'])->middleware('permission:journal.bill_entry');
    Route::post('/', [JournalController::class, 'store'])->middleware('permission:journal.bill_entry');
    Route::post('{journal}/approve', [JournalController::class, 'approve'])->middleware('permission:journal.approve');
    Route::post('{journal}/return', [JournalController::class, 'return'])->middleware('permission:journal.approve');
    Route::post('{journal}/resubmit', [JournalController::class, 'resubmit'])->middleware('permission:journal.bill_entry');
    Route::post('{journal}/pay', [JournalController::class, 'pay'])->middleware('permission:journal.payment');
    Route::post('{journal}/reverse', [JournalController::class, 'reverse'])->middleware('permission:journal.reverse');
    Route::get('{journal}', [JournalController::class, 'show'])->middleware('permission:journal.view');
});
