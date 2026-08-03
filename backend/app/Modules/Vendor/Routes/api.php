<?php

use App\Modules\Vendor\Controllers\Api\VendorController;
use Illuminate\Support\Facades\Route;

Route::crud('vendors', VendorController::class, 'vendor');
Route::get('/vendor-data', [VendorController::class, 'getVendorData'])->middleware('permission:vendor.view');
