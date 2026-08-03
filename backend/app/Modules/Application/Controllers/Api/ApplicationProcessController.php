<?php

namespace App\Modules\Application\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Application\Services\ApplicationProcessService;
use App\Modules\Application\Requests\ApplicationProcessRequest;
use App\Modules\Application\Http\Resources\ApplicationProcessResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationProcessController extends Controller
{
    protected ApplicationProcessService $service;

    public function __construct(ApplicationProcessService $service)
    {
        $this->service = $service;
    }

    /* ==========================================================
     | List
     |========================================================== */

    // public function index(Request $request)
    // {

    //     $processes = $this->service->getAll($request->all());

    //     return ApplicationProcessResource::collection($processes);
    // }

    /* ==========================================================
     | Store
     |========================================================== */

    // public function store(ApplicationProcessRequest $request)
    // {
    //     $process = $this->service->create($request->validated());

    //     return new ApplicationProcessResource($process);
    // }

    public function bulkOfferExtend(Request $request): JsonResponse
    {
        $record = $this->service->bulkOfferExtend($request->job_id, $request->process_id, $request->ids);

        return response()->json([
            'success' => true,
            'message' => 'Bulk offer extended successfully.',
        ]);
    }

    /* ==========================================================
     | Show
     |========================================================== */

    // public function show(int $id)
    // {
    //     $process = $this->service->getById($id);

    //     return new ApplicationProcessResource($process);
    // }

    /* ==========================================================
     | Update
     |========================================================== */

    // public function update(ApplicationProcessRequest $request, int $id)
    // {
    //     $process = $this->service->update($id, $request->validated());

    //     return new ApplicationProcessResource($process);
    // }

    /* ==========================================================
     | Delete
     |========================================================== */

    // public function destroy(int $id)
    // {
    //     $this->service->delete($id);

    //     return response()->json([
    //         'message' => 'Application process deleted successfully.',
    //     ]);
    // }
}
