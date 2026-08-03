<?php

namespace App\Modules\Agent\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Agent\Models\Agent;
use App\Modules\Agent\Requests\AgentRequest;
use App\Modules\Agent\Resources\AgentResource;
use App\Modules\Agent\Services\AgentService;
use App\Http\Requests\ApiIndexRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Modules\Agent\Contracts\AgentDataServiceInterface;
use App\Modules\Application\Services\ApplicationDataDbService;
use App\Modules\Reports\Services\AgentDbService;
use App\Modules\Shared\Helpers\FileHelper;

class AgentController extends Controller
{
    public function __construct(private readonly AgentService $service, private readonly AgentDataServiceInterface $agentDataService, private readonly ApplicationDataDbService $applicationDataDbService, private readonly AgentDbService $agentDbService) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();
        $filters['status'] = $request->get('status');
        $filters['has_ats_applications'] = $request->get('has_ats_applications');
        $filters['include_ats_count'] = $request->get('include_ats_count');
        $user = auth()->user();

        // 🔥 Force client isolation
        if ($user->type === 'agent') {
            $filters['agent_id'] = $user->agent->id;
        }

        $agentData = $this->service->getPaginatedDataWithCache($filters);

        return AgentResource::collection($agentData);
    }





    public function store(AgentRequest $request): JsonResponse
    {
        $storedFiles = []; // Keep track of stored files in case we need to rollback
        try {
            $data = $request->validated(); // Get validated input

            if ($request->hasFile('agent_image_path')) {
                $path = FileHelper::store($request->file('agent_image_path'), 'agents');
                $data['agent_image_path'] = $path;
                $storedFiles['agent_image_path'] = $path;
            }
            $record = $this->service->createAgent($data);
            $this->applicationDataDbService->clearApplicationDataCache();
            $this->agentDataService->clearAgentDataCache();
            $this->agentDbService->clearAgentCache();

            return apiSuccess(new AgentResource($record), 'created', 201);
        } catch (\Exception $e) {
            // Rollback any stored files
            foreach ($storedFiles as $filePath) {
                FileHelper::delete($filePath);
            }
            throw $e;
        }
    }

    public function show(Agent $agent): AgentResource
    {
        $this->authorizeAgentAccess($agent);

        return new AgentResource($this->service->getAgent($agent));
    }



    public function update(AgentRequest $request, int $id): JsonResponse
{

    $storedFiles = [];

    try {
        $agent = $this->service->getById($id);
        $this->authorizeAgentAccess($agent);

        $data = $request->validated();

        // -----------------------------
        // IMAGE UPDATE LOGIC
        // -----------------------------
        if ($request->hasFile('agent_image_path')) {

            // delete old image if exists
            if (!empty($agent->agent_image_path)) {
                FileHelper::delete($agent->agent_image_path);
            }

            // store new image
            $path = FileHelper::store(
                $request->file('agent_image_path'),
                'agents'
            );

            $data['agent_image_path'] = $path;
            $storedFiles['agent_image_path'] = $path;
        }

        // -----------------------------
        // UPDATE AGENT
        // -----------------------------
        $record = $this->service->updateAgent($id, $data);

        // -----------------------------
        // CACHE CLEAR
        // -----------------------------
        $this->applicationDataDbService->clearApplicationDataCache();
        $this->agentDataService->clearAgentDataCache();
        $this->agentDbService->clearAgentCache();

        return apiSuccess(new AgentResource($record), 'updated');

    } catch (\Exception $e) {

        // rollback new uploaded file if error happens
        foreach ($storedFiles as $filePath) {
            FileHelper::delete($filePath);
        }

        throw $e;
    }
}
public function destroy(int $id): JsonResponse
{
    try {
        $agent = $this->service->getById($id);
        $this->authorizeAgentAccess($agent);

        // -----------------------------
        // DELETE FILE FIRST (or store path)
        // -----------------------------
        $filePath = $agent->agent_image_path;

        // -----------------------------
        // DELETE AGENT FROM DB
        // -----------------------------
        $this->service->deleteAgent($id);

        // -----------------------------
        // DELETE FILE (AFTER DB SUCCESS)
        // -----------------------------
        if (!empty($filePath)) {
            FileHelper::delete($filePath);
        }

        // -----------------------------
        // CLEAR CACHE
        // -----------------------------
        $this->applicationDataDbService->clearApplicationDataCache();
        $this->agentDataService->clearAgentDataCache();
        $this->agentDbService->clearAgentCache();

        return apiSuccess(null, 'deleted');

    } catch (\Exception $e) {
        throw $e;
    }
}

    public function bulkDelete(Request $request): JsonResponse
{
    try {
        $ids = $request->ids;

        // -----------------------------
        // GET FILE PATHS BEFORE DELETE
        // -----------------------------
        $agents = $this->service->getByIds($ids); // you need this method
        $filePaths = $agents->pluck('agent_image_path')->filter()->toArray();

        // -----------------------------
        // DELETE FROM DB
        // -----------------------------
        $this->service->bulkDelete($ids);

        // -----------------------------
        // DELETE FILES
        // -----------------------------
        foreach ($filePaths as $filePath) {
            FileHelper::delete($filePath);
        }

        // -----------------------------
        // CLEAR CACHE
        // -----------------------------
        $this->applicationDataDbService->clearApplicationDataCache();
        $this->agentDataService->clearAgentDataCache();
        $this->agentDbService->clearAgentCache();

        return apiSuccess(null, 'deleted', 200, 'Records');

    } catch (\Exception $e) {
        throw $e;
    }
}

    public function getAgentData(): JsonResponse
    {
        $data = $this->agentDataService->getAgentData();

        return apiSuccess($data, 'fetched');
    }

    private function authorizeAgentAccess($agent): void
    {
        $user = auth()->user();

        if ($user->type === 'agent') {
            if (!$user->agent || $agent->id !== $user->agent->id) {
                abort(403, 'Unauthorized access');
            }
        }
    }


    public function getAllAgentsWithPoints()
    {
        $agents = $this->service->getAllAgentsWithPoints();
        return apiSuccess(AgentResource::collection($agents), 'Agent list with points');
    }

    public function getTopAgentByPoints()
    {
        $agent = $this->service->getTopAgentByPoints();

        if (!$agent) {
            return apiSuccess(null, 'fetched', 200, 'Top agent');
        }

        return apiSuccess(new AgentResource($agent), 'fetched', 200, 'Top agent');
    }

}
