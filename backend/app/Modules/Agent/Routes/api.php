<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Agent\Controllers\Api\AgentController;



Route::crud('agents', AgentController::class, 'agent');
Route::get('agents-data', [AgentController::class, 'getAgentData'])->middleware('permission:agent.view');
Route::get('/agent-all-data-with-points', [AgentController::class, 'getAllAgentsWithPoints'])->middleware('permission:agent.view');
Route::get('/agents-top-performer', [AgentController::class, 'getTopAgentByPoints'])->middleware('permission:agent.view');
