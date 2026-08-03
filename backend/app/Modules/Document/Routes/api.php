<?php

use App\Modules\Document\Controllers\Api\DocumentController;
use Illuminate\Support\Facades\Route;

Route::get('documents/categories', [DocumentController::class, 'categories'])
    ->middleware('permission:document.view');

Route::get('documents/{document}/download', [DocumentController::class, 'download'])
    ->middleware('permission:document.download');

Route::crud('documents', DocumentController::class, 'document');
