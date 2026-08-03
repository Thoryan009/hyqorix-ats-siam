<?php

use Illuminate\Support\Facades\Route;
use App\Modules\WorkOrder\Controllers\Api\WorkOrderController;




Route::crud('work-orders', WorkOrderController::class, 'demand_letter');
Route::get('/workorder-clients', [WorkOrderController::class, 'getWorkOrderClients'])->middleware('permission:demand_letter.view');
Route::get('work-orders-data', [WorkOrderController::class, 'getWorkOrderData'])
    ->middleware('permission:demand_letter.view');
