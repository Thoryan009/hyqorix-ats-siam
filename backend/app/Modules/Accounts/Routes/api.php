<?php

use App\Modules\Accounts\Controllers\Api\BalanceSheetController;
use App\Modules\Accounts\Controllers\Api\ChartOfAccountController;
use App\Modules\Accounts\Controllers\Api\GrossProfitController;
use App\Modules\Accounts\Controllers\Api\IncomeStatementController;
use App\Modules\Accounts\Controllers\Api\TrialBalanceController;
use Illuminate\Support\Facades\Route;

Route::get('trial-balance', [TrialBalanceController::class, 'index'])->middleware('permission:accounts.trial_balance');
Route::get('gross-profit', [GrossProfitController::class, 'index'])->middleware('permission:accounts.gross_profit');
Route::get('gross-profit/export-pdf', [GrossProfitController::class, 'exportPdf'])->middleware('permission:accounts.gross_profit');
Route::get('income-statement', [IncomeStatementController::class, 'index'])->middleware('permission:accounts.income_statement');
Route::get('balance-sheet', [BalanceSheetController::class, 'index'])->middleware('permission:accounts.balance_sheet');

Route::prefix('chart-of-accounts')->group(function () {
    Route::get('/', [ChartOfAccountController::class, 'index'])->middleware('permission:chart_of_account.view');
    Route::post('/', [ChartOfAccountController::class, 'store'])->middleware('permission:chart_of_account.create');
    Route::post('bulk-delete', [ChartOfAccountController::class, 'bulkDelete'])->middleware('permission:chart_of_account.delete');
    Route::get('{chartOfAccount}', [ChartOfAccountController::class, 'show'])->middleware('permission:chart_of_account.view');
    Route::put('{chartOfAccount}', [ChartOfAccountController::class, 'update'])->middleware('permission:chart_of_account.edit');
    Route::delete('{chartOfAccount}', [ChartOfAccountController::class, 'destroy'])->middleware('permission:chart_of_account.delete');
})->middleware('permission:chart_of_account.view');
