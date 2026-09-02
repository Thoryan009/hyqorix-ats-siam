<?php

use App\Modules\Accounts\Controllers\Api\BalanceSheetController;
use App\Modules\Accounts\Controllers\Api\ChartOfAccountController;
use App\Modules\Accounts\Controllers\Api\GrossProfitController;
use App\Modules\Accounts\Controllers\Api\IncomeStatementController;
use App\Modules\Accounts\Controllers\Api\TrialBalanceController;
use Illuminate\Support\Facades\Route;

Route::get('trial-balance', [TrialBalanceController::class, 'index']);
Route::get('gross-profit', [GrossProfitController::class, 'index']);
Route::get('gross-profit/export-pdf', [GrossProfitController::class, 'exportPdf']);
Route::get('income-statement', [IncomeStatementController::class, 'index']);
Route::get('balance-sheet', [BalanceSheetController::class, 'index']);

Route::prefix('chart-of-accounts')->group(function () {
    Route::get('/', [ChartOfAccountController::class, 'index']);
    Route::post('/', [ChartOfAccountController::class, 'store']);
    Route::post('bulk-delete', [ChartOfAccountController::class, 'bulkDelete']);
    Route::get('{chartOfAccount}', [ChartOfAccountController::class, 'show']);
    Route::put('{chartOfAccount}', [ChartOfAccountController::class, 'update']);
    Route::delete('{chartOfAccount}', [ChartOfAccountController::class, 'destroy']);
});
