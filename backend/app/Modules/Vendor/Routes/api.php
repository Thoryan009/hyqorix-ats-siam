<?php

use App\Modules\Vendor\Controllers\Api\VendorController;
use App\Modules\Vendor\Controllers\Api\VendorTypeController;
use Illuminate\Support\Facades\Route;

Route::crud('vendors', VendorController::class, 'vendor');
Route::crud('vendor-types', VendorTypeController::class, 'vendor');
Route::get('/vendor-data', [VendorController::class, 'getVendorData'])->middleware('permission:vendor.view');
