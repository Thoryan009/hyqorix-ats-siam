<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Application\Controllers\Api\ApplicationController;
use App\Modules\Application\Controllers\Api\TransactionController;
use App\Modules\Application\Controllers\Api\CandidateBillController;
use App\Modules\Application\Controllers\Api\ApplicationProcessController;
use App\Modules\Application\Controllers\Api\ClientBillController;
use App\Modules\Application\Controllers\Api\ProcessController;
use App\Modules\Application\Controllers\Api\ClientMailController;
use App\Modules\Application\Controllers\Api\ClientTransactionController;
use App\Modules\Application\Controllers\Api\SubjectController;
use App\Modules\Application\Controllers\Api\QualificationController;
use App\Modules\Application\Controllers\Api\EmbasySubmissionController;
use App\Modules\Application\Controllers\Api\EmbassyListController;

/*
|--------------------------------------------------------------------------
| APPLICATION MODULE ROUTES
|--------------------------------------------------------------------------
*/

// CRUD Routes
Route::crud('applications', ApplicationController::class, 'application');
Route::post('/applications/{id}/merge-documents', [ApplicationController::class, 'mergeDocuments']);

// File Handling
Route::post('/applications/job-lists/{job_id}/agents/{agent_id}/bulk-upload', [ApplicationController::class, 'bulkUpload'])->middleware('permission:application.bulk_upload');

Route::post('/applications/delete-file/{id}', [ApplicationController::class, 'deleteFile'])->middleware('permission:application.delete');

// Bulk Operations
Route::post('/applications/bulk-status-update', [ApplicationController::class, 'bulkStatusUpdate'])->middleware('permission:application.bulk_status_update');

Route::post('/applications/bulk-offer-extend', [ApplicationProcessController::class, 'bulkOfferExtend'])->middleware('permission:application.offer_extend');

// OCR
Route::post('/applications/passport/ocr', [ApplicationController::class, 'passportOCR'])->middleware('permission:application.create');
Route::get('/applications/passport/check-exists', [ApplicationController::class, 'checkPassportNoExists']);

// Data Fetching
Route::get('/applications-processes', [ApplicationController::class, 'getAllProcesses'])->middleware('permission:application_process.view');

Route::get('applications-data', [ApplicationController::class, 'getApplicationData'])->middleware('permission:application.view');
Route::get('hiring-list-data', [ApplicationController::class, 'getHiringListData'])->middleware('permission:application.view');
Route::get('application-list-data', [ApplicationController::class, 'getApplicationListData'])->middleware('permission:application.view_application_list');
Route::get('short-list-data', [ApplicationController::class, 'getShortListData'])->middleware('permission:application.view_short_list');
Route::get('waiting-list-data', [ApplicationController::class, 'getWaitingListData'])->middleware('permission:application.view_waiting_list');
Route::get('rejected-list-data', [ApplicationController::class, 'getRejectedListData'])->middleware('permission:application.view_rejected_list');
Route::get('embassy-hints', [ApplicationController::class, 'getEmbassyHints'])->middleware('permission:visa_processing.view');
Route::crud('embassy-lists', EmbassyListController::class, 'embassy_list');

/*
|--------------------------------------------------------------------------
| MASTER DATA MODULE ROUTES
|--------------------------------------------------------------------------
*/
Route::crud('subjects', SubjectController::class, 'subject');
Route::crud('qualifications', QualificationController::class, 'qualification');
Route::crud('processes', ProcessController::class, 'process');

/*
|--------------------------------------------------------------------------
| CLIENT MODULE ROUTES
|--------------------------------------------------------------------------
*/

// Client Mail
Route::crud('client-mails', ClientMailController::class, 'client_mail');

// Client Transactions
Route::crud('client-transactions', ClientTransactionController::class, 'client_transaction');

/*
|--------------------------------------------------------------------------
| TRANSACTION MODULE ROUTES
|--------------------------------------------------------------------------
*/

// CRUD
Route::crud('transactions', TransactionController::class, 'transaction');

// Custom Data APIs
Route::get('transaction/data', [TransactionController::class, 'getTransactionData'])->middleware('permission:transaction.view');

Route::get('pos-transactions', [TransactionController::class, 'applicationTransactions'])->middleware('permission:transaction.view');

/*
|--------------------------------------------------------------------------
| CANDIDATE BILLING MODULE
|--------------------------------------------------------------------------
*/

// CRUD
Route::crud('candidate-bills', CandidateBillController::class, 'candidate_bill');

// Extra APIs
Route::get('candidate-bills-job-lists', [CandidateBillController::class, 'getCandidateBillJobLists'])->middleware('permission:candidate_bill.view');

/*
|--------------------------------------------------------------------------
| CLIENT BILLING MODULE
|--------------------------------------------------------------------------
*/

// View
Route::get('client-bills', [ClientBillController::class, 'index'])->middleware('permission:client_bill.view');

Route::get('client-bills/data', [ClientBillController::class, 'getClientBillData'])->middleware('permission:client_bill.view');

Route::get('client-bills/{billNo}', [ClientBillController::class, 'getByBillNo'])->middleware('permission:client_bill.view');

// Actions
Route::post('client-bills/generate-invoice', [ClientBillController::class, 'generateInvoice'])->middleware('permission:client_bill.create');

Route::post('client-bills/collect-invoice', [ClientBillController::class, 'collectInvoice'])->middleware('permission:client_bill.update');

Route::post('client-bills/cancel-invoice', [ClientBillController::class, 'cancelledInvoice'])->middleware('permission:client_bill.update');

Route::post('client-bills/send-mail', [ClientBillController::class, 'updateInvoiceAndSendMail'])->middleware('permission:client_bill.update');

// Route::apiResource('embasy-submissions', EmbasySubmissionController::class);
Route::post('embasy-submissions/bulk-delete', [EmbasySubmissionController::class, 'bulkDelete']);
Route::post('embasy-submissions/generate-visa-profession-en', [EmbasySubmissionController::class, 'generateVisaProfessionEnglish']);
Route::put('embasy-submissions/{applicationId}', [EmbasySubmissionController::class, 'updateEmbassySubmission']);
// ->middleware('permission:application.embassy_submission_edit');
Route::delete(
    'embasy-submissions/{applicationId}',
    [EmbasySubmissionController::class, 'deleteEmbassySubmission']
);
// ->middleware('permission:application.embassy_submission_delete');
Route::get(
    'embasy-submissions/{applicationId}',
    [EmbasySubmissionController::class, 'getMofaInformations']
);
Route::get(
    'embasy-submissions',
    [EmbasySubmissionController::class, 'index']
);
Route::get(
    'embasy-submissions-ksa-data',
    [EmbasySubmissionController::class, 'getKSAData']
);
Route::post('embasy-submissions-bulk-status-update', [EmbasySubmissionController::class, 'bulkStatusUpdate']);
