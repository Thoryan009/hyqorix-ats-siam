<?php

use App\Modules\Finance\Controllers\Api\ExpenseCategoryController;
use App\Modules\Finance\Controllers\Api\ExpenseHeadController;
use App\Modules\Finance\Controllers\Api\IncomeCategoryController;
use App\Modules\Finance\Controllers\Api\IncomeHeadController;
use App\Modules\Finance\Controllers\Api\FinanceAccountController;
use App\Modules\Finance\Controllers\Api\FinanceAccountMovementController;
use App\Modules\Finance\Controllers\Api\FinanceAccountTypeTransactionController;
use App\Modules\Finance\Controllers\Api\FinanceBankController;
use App\Modules\Finance\Controllers\Api\FinanceBillEntryController;
use App\Modules\Finance\Controllers\Api\FinanceIncomeCollectionController;
use App\Modules\Finance\Controllers\Api\FinanceGrossProfitReportController;
use App\Modules\Finance\Controllers\Api\FinanceIncomeStatementController;
use App\Modules\Finance\Controllers\Api\FinanceBalanceSheetController;
use App\Modules\Finance\Controllers\Api\FinanceTrialBalanceController;
use Illuminate\Support\Facades\Route;

Route::get('finance-reports/gross-profit', [FinanceGrossProfitReportController::class, 'index'])
    ->middleware('permission:gross_profit_report.view');
Route::get('finance-reports/gross-profit/export-csv', [FinanceGrossProfitReportController::class, 'exportCsv'])
    ->middleware('permission:gross_profit_report.view');
Route::get('finance-reports/gross-profit/export-pdf', [FinanceGrossProfitReportController::class, 'exportPdf'])
    ->middleware('permission:gross_profit_report.view');
Route::get('finance-reports/trial-balance', [FinanceTrialBalanceController::class, 'index'])
    ->middleware('permission:trial_balance.view');
Route::get('finance-reports/income-statement', [FinanceIncomeStatementController::class, 'index'])
    ->middleware('permission:income_statement.view');
Route::get('finance-reports/balance-sheet', [FinanceBalanceSheetController::class, 'index'])
    ->middleware('permission:balance_sheet.view');

Route::get('finance-income-collections', [FinanceIncomeCollectionController::class, 'index'])
    ->middleware('permission:receive_payment.create|income_list.view');
Route::get('finance-income-collections/summary', [FinanceIncomeCollectionController::class, 'summary'])
    ->middleware('permission:receive_payment.create|income_list.view');
Route::post('finance-income-collections', [FinanceIncomeCollectionController::class, 'store'])
    ->middleware('permission:receive_payment.create');

Route::prefix('expense-categories')->group(function () {
    Route::get('/', [ExpenseCategoryController::class, 'index'])
        ->middleware('permission:account_setup.view|bill_generation.create');
    Route::post('/', [ExpenseCategoryController::class, 'store'])
        ->middleware('permission:account_setup.create');
    Route::get('{expenseCategory}', [ExpenseCategoryController::class, 'show'])
        ->middleware('permission:account_setup.view|bill_generation.create');
    Route::put('{expenseCategory}', [ExpenseCategoryController::class, 'update'])
        ->middleware('permission:account_setup.edit');
});
Route::prefix('expense-heads')->group(function () {
    Route::get('/', [ExpenseHeadController::class, 'index'])
        ->middleware('permission:account_setup.view|bill_generation.create');
    Route::get('/summary', [ExpenseHeadController::class, 'summary'])
        ->middleware('permission:account_setup.view|bill_generation.create');
    Route::post('/', [ExpenseHeadController::class, 'store'])
        ->middleware('permission:account_setup.create');
    Route::get('{expenseHead}', [ExpenseHeadController::class, 'show'])
        ->middleware('permission:account_setup.view|bill_generation.create');
    Route::put('{expenseHead}', [ExpenseHeadController::class, 'update'])
        ->middleware('permission:account_setup.edit');
});
Route::prefix('income-categories')->group(function () {
    Route::get('/', [IncomeCategoryController::class, 'index'])
        ->middleware('permission:account_setup.view');
    Route::get('{incomeCategory}', [IncomeCategoryController::class, 'show'])
        ->middleware('permission:account_setup.view');
    Route::put('{incomeCategory}', [IncomeCategoryController::class, 'update'])
        ->middleware('permission:account_setup.edit');
});
Route::prefix('income-heads')->group(function () {
    Route::get('/', [IncomeHeadController::class, 'index'])
        ->middleware('permission:account_setup.view');
    Route::get('/summary', [IncomeHeadController::class, 'summary'])
        ->middleware('permission:account_setup.view');
    Route::post('/', [IncomeHeadController::class, 'store'])
        ->middleware('permission:account_setup.create');
    Route::get('{incomeHead}', [IncomeHeadController::class, 'show'])
        ->middleware('permission:account_setup.view');
    Route::put('{incomeHead}', [IncomeHeadController::class, 'update'])
        ->middleware('permission:account_setup.edit');
});
Route::prefix('finance-account-type-transactions')->group(function () {
    Route::get('/', [FinanceAccountTypeTransactionController::class, 'index'])
        ->middleware('permission:receive_payment.create|transaction.view');
    Route::get('/summary', [FinanceAccountTypeTransactionController::class, 'summary'])
        ->middleware('permission:receive_payment.create|transaction.view');
    Route::post('/', [FinanceAccountTypeTransactionController::class, 'store'])
        ->middleware('permission:receive_payment.create');
});

Route::crud('finance-banks', FinanceBankController::class, 'finance_bank');

Route::get('finance-accounts/summary', [FinanceAccountController::class, 'summary'])
    ->middleware('permission:finance_account.view|bill_generation.create');
Route::get('finance-accounts/capital', [FinanceAccountController::class, 'capital'])
    ->middleware('permission:finance_account.view');
Route::get('finance-accounts/sale', [FinanceAccountController::class, 'sale'])
    ->middleware('permission:finance_account.view');
Route::get('finance-accounts/bills-receivable-ledger', [FinanceAccountController::class, 'billsReceivableLedger'])
    ->middleware('permission:finance_account.view');

Route::post('finance-accounts/transfer', [FinanceAccountMovementController::class, 'transfer'])
    ->middleware('permission:finance_account.edit');
Route::post('finance-accounts/deposit', [FinanceAccountMovementController::class, 'deposit'])
    ->middleware('permission:finance_account.edit');
Route::post('finance-accounts/withdraw', [FinanceAccountMovementController::class, 'withdraw'])
    ->middleware('permission:finance_account.edit');
Route::post('finance-accounts/collect-payment', [FinanceAccountMovementController::class, 'collectPayment'])
    ->middleware('permission:finance_account.create');
Route::get('finance-accounts/sale-collection-summary', [FinanceAccountMovementController::class, 'saleCollectionSummary'])
    ->middleware('permission:finance_account.view');
Route::get('finance-accounts/sale-collections', [FinanceAccountMovementController::class, 'saleCollections'])
    ->middleware('permission:receive_list.view|finance_account.view');
Route::get('finance-accounts/bills-receivable', [FinanceAccountMovementController::class, 'billsReceivable'])
    ->middleware('permission:finance_account.view');
Route::get('finance-accounts/bills-receivable/{applicationId}', [FinanceAccountMovementController::class, 'showBillReceivable'])
    ->middleware('permission:finance_account.view')
    ->where('applicationId', '-?[0-9]+');
Route::get('finance-accounts/{financeAccount}/ledger', [FinanceAccountMovementController::class, 'ledger'])
    ->middleware('permission:finance_account.view');
Route::get('finance-accounts/{financeAccount}/ledger/export-csv', [FinanceAccountMovementController::class, 'exportLedgerCsv'])
    ->middleware('permission:finance_account.view');
Route::get('finance-accounts/{financeAccount}/ledger/export-pdf', [FinanceAccountMovementController::class, 'exportLedgerPdf'])
    ->middleware('permission:finance_account.view');

Route::crud('finance-accounts', FinanceAccountController::class, 'finance_account');

// Override CRUD read routes so bill generation can load accounts without finance_account.view.
// Must be registered after Route::crud — same URI replaces the earlier route.
Route::get('finance-accounts', [FinanceAccountController::class, 'index'])
    ->middleware('permission:finance_account.view|bill_generation.create');
Route::get('finance-accounts/{financeAccount}', [FinanceAccountController::class, 'show'])
    ->middleware('permission:finance_account.view|bill_generation.create');

Route::prefix('finance-bill-entries')->group(function () {
    Route::get('/', [FinanceBillEntryController::class, 'index'])
        ->middleware('permission:bill_generation.create|submitted_bills.view|paid_bill.view|rejected_bills.view');
    Route::get('/summary', [FinanceBillEntryController::class, 'summary'])
        ->middleware('permission:bill_generation.create|submitted_bills.view|paid_bill.view|rejected_bills.view');
    Route::get('/head-total/{headId}', [FinanceBillEntryController::class, 'headTotal'])
        ->middleware('permission:bill_generation.create|rejected_bills.view');
    Route::post('/', [FinanceBillEntryController::class, 'store'])
        ->middleware('permission:bill_generation.create');
    Route::post('/batch', [FinanceBillEntryController::class, 'storeBatch'])
        ->middleware('permission:bill_generation.create');
    Route::post('/multi-head', [FinanceBillEntryController::class, 'storeMultiHead'])
        ->middleware('permission:bill_generation.create');
    Route::get('{financeBillEntry}', [FinanceBillEntryController::class, 'show'])
        ->middleware('permission:|bill_generation.create|submitted_bills.view|paid_bill.view|rejected_bills.view');
    Route::put('{financeBillEntry}', [FinanceBillEntryController::class, 'update'])
        ->middleware('permission:transaction.edit');
    Route::post('{financeBillEntry}/approve', [FinanceBillEntryController::class, 'approve'])
        ->middleware('permission:receive_payment.create|submitted_bills.approve');
    Route::post('{financeBillEntry}/pay-payable', [FinanceBillEntryController::class, 'payPayable'])
        ->middleware('permission:receive_payment.create');
    Route::post('manager-approve-batch', [FinanceBillEntryController::class, 'managerApproveBatch'])
        ->middleware('permission:submitted_bills.approve');
    Route::post('{financeBillEntry}/manager-approve', [FinanceBillEntryController::class, 'managerApprove'])
        ->middleware('permission:submitted_bills.approve');
    Route::post('{financeBillEntry}/reject', [FinanceBillEntryController::class, 'reject'])
        ->middleware('permission:receive_payment.create|submitted_bills.approve');
});
