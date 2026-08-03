<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Reports\Employee\Controllers\EmployeeReportController;
use App\Modules\Reports\Transaction\Controllers\TransactionReportController;
use App\Modules\Reports\ATS\Controllers\ATSReportController;
use App\Modules\Reports\Controller\ReportController;
use App\Modules\Reports\Application\Controllers\ApplicationReportController;
use App\Modules\Reports\Expiry\Controllers\ExpiryReportController;
use App\Modules\Reports\Tasheer\Controllers\TasheerAppointmentReportController;

Route::prefix('reports/transactions')->group(function () {
    Route::get('/', [TransactionReportController::class, 'index']);
});

Route::prefix('reports/employees')->group(function () {
    Route::get('/', [EmployeeReportController::class, 'index']);
});

Route::prefix('reports/ats')->group(function () {
    Route::get('/', [ATSReportController::class, 'index']);
    Route::get('/show', [ATSReportController::class, 'show']);
    Route::get('/export-csv', [ATSReportController::class, 'exportCSV']);
});

Route::prefix('reports/transaction')->group(function () {
    Route::get('/', [TransactionReportController::class, 'index']);
    Route::get('/show', [TransactionReportController::class, 'show']);
});

Route::prefix('reports/application')->group(function () {
    Route::get('/', [ApplicationReportController::class, 'index']);
    Route::get('/show', [ApplicationReportController::class, 'show']);
    Route::get('/export-csv', [ApplicationReportController::class, 'exportCSV']);
});

Route::prefix('reports/expiry')->group(function () {
    Route::get('/', [ExpiryReportController::class, 'index']);
    Route::get('/filter-data', [ExpiryReportController::class, 'filterData']);
    Route::get('/export-csv', [ExpiryReportController::class, 'exportCSV']);
});

Route::prefix('reports/tasheer-appointment')->group(function () {
    Route::get('/', [TasheerAppointmentReportController::class, 'index'])->middleware('permission:visa_processing.view');
    Route::get('/filter-data', [TasheerAppointmentReportController::class, 'filterData'])->middleware('permission:visa_processing.view');
    Route::get('/export-csv', [TasheerAppointmentReportController::class, 'exportCsv'])->middleware('permission:visa_processing.view_report');
    Route::post('/bulk-status-update', [TasheerAppointmentReportController::class, 'bulkStatusUpdate'])->middleware('permission:visa_processing.edit');
});

Route::get('/report-ats-summary', [ATSReportController::class, 'getAtsSummaryReport']);
Route::get('/report-ats-summary/export-csv', [ATSReportController::class, 'exportAtsSummaryReportCsv']);
Route::get('/report-ats-summary/filter-data', [ATSReportController::class, 'getAtsSummaryReport']);

Route::get('/report-countries', [ReportController::class, 'getCountryReport']);
Route::get('/report-clients', [ReportController::class, 'getClientReport']);
Route::get('/report-agents', [ReportController::class, 'getAgentReport']);
Route::get('/report-principals', [ReportController::class, 'getPrincipalData']);
Route::get('/report-processes', [ReportController::class, 'getProcessReport']);
Route::get('/report-jobs', [ReportController::class, 'getJobReport']);
Route::get('/report-work-orders', [ReportController::class, 'getWorkOrderReport']);
Route::get('/report-payment-methods', [ReportController::class, 'getTransactionPaymentMethodReport']);
Route::get('/report-transaction-status', [ReportController::class, 'getTransactionStatusReport']);
Route::get('/report-data', [ReportController::class, 'getReportData']);
Route::get('/report-expiry', [ReportController::class, 'getExpiryReport']);
