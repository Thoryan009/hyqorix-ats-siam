<?php

use App\Modules\JobList\Controllers\Api\AtsController;
use Illuminate\Support\Facades\Route;
use App\Modules\JobList\Controllers\Api\JobListController;
use App\Modules\JobList\Controllers\Api\JobListDetailController;
use App\Modules\JobList\Controllers\Api\JobListDetailsHeadController;
use App\Modules\JobList\Controllers\Api\JobListDetailsCategoryController;



Route::crud('job-lists', JobListController::class, 'job');
Route::get('/job-lists-data', [JobListController::class, 'getJobListData'])->middleware('permission:job.view');

Route::get('/ats', [AtsController::class, 'index'])->middleware('permission:ats.view');
Route::get('/ats-data', [AtsController::class, 'getAtsData'])->middleware('permission:ats.view');
Route::get('/ats/single', [AtsController::class, 'show'])->middleware('permission:ats.view');
Route::get('/ats-quick-search', [AtsController::class, 'quickSearch'])->middleware('permission:ats.view');
Route::get('/ats/{id}', [AtsController::class, 'show'])->middleware('permission:ats.view');

Route::post('/ats/update-process', [AtsController::class, 'updateJobProcessForm'])->middleware('permission:ats.update');
Route::post('/ats/next-process', [AtsController::class, 'createNextProcess'])->middleware('permission:ats.update');
Route::post('/ats/bulk-next-process', [AtsController::class, 'createBulkNextProcess'])->middleware('permission:ats.update');
Route::delete('/ats/delete-current-process', [AtsController::class, 'deleteCurrentApplicationProcess'])->middleware('permission:ats.delete');



Route::get('/ats-clients', [AtsController::class, 'getAtsClients'])->middleware('permission:ats.view');
Route::get('/ats-work-orders', [AtsController::class, 'getAtsWorkOrders'])->middleware('permission:ats.view');



Route::crud('job-list-details', JobListDetailController::class, 'job_detail');
Route::post('job-list-details/bulk-create', [JobListDetailController::class, 'createBulk'])->middleware('permission:job_detail.create');


Route::get('/job-list-detail-heads', [JobListDetailController::class, 'getJobListDetailHeads'])->middleware('permission:fee_head.view');

Route::crud('job-list-details-heads', JobListDetailsHeadController::class, 'fee_head');

Route::crud('job-list-details-categories', JobListDetailsCategoryController::class, 'fee_category');

Route::get('/job-list-details-head-categories', [JobListDetailsHeadController::class, 'getJobListHeadCategories'])->middleware('permission:fee_head.view');





/*
|--------------------------------------------------------------------------
| Admin / Recruiter Full Access
|--------------------------------------------------------------------------
*/

// Route::middleware('permission:super_admin,admin,recruiter')->group(function () {




//     // Route::prefix('ats')->group(function () {
//     //     Route::get('/single', [AtsController::class, 'show']);
//     //     Route::post('/update-process', [AtsController::class, 'updateJobProcessForm']);
//     //     Route::post('/next-process', [AtsController::class, 'createNextProcess']);
//     //     Route::post('/bulk-next-process', [AtsController::class, 'createBulkNextProcess']);
//     //     Route::delete('/delete-current-process', [AtsController::class, 'deleteCurrentApplicationProcess']);
//     // });

//     // Route::get('/ats-clients', [AtsController::class, 'getAtsClients']);
//     // Route::get('/ats-work-orders', [AtsController::class, 'getAtsWorkOrders']);



//     // Route::apiResource('job-list-details', JobListDetailController::class);
//     // Route::post('job-list-details/bulk-delete', [JobListDetailController::class, 'bulkDelete']);
//     // Route::post('job-list-details/bulk-create', [JobListDetailController::class, 'createBulk']);

//     // Route::get('/job-list-detail-heads', [JobListDetailController::class, 'getJobListDetailHeads']);

//     // Route::apiResource('job-list-details-heads', JobListDetailsHeadController::class);
//     // Route::post('job-list-details-heads/bulk-delete', [JobListDetailsHeadController::class, 'bulkDelete']);
//     // Route::get('/job-list-details-head-categories', [JobListDetailsHeadController::class, 'getJobListHeadCategories']);

//     // Route::apiResource('job-list-details-categories', JobListDetailsCategoryController::class);
//     // Route::post('job-list-details-categories/bulk-delete', [JobListDetailsCategoryController::class, 'bulkDelete']);
// });
