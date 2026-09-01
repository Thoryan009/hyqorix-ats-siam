<?php

namespace App\Modules\Agent\Services;

use Illuminate\Support\Facades\DB;
use App\Modules\Agent\Models\Agent;
use App\Services\BaseCachedService;
use App\Modules\Agent\Repositories\AgentRepository;
use App\Modules\Parties\Services\PartyService;

class AgentService extends BaseCachedService
{
    public function __construct(
        protected AgentRepository $repository,
        protected PartyService $partyService,
    ) {
        parent::__construct(new Agent());
    }

    /* ==========================================================
     | Read Operations (Cached)
     |========================================================== */

    public function getPaginatedDataWithCache(array $filters = [])
    {
        return $this->remember(
            $this->filtersCacheKey($filters),
            fn() => $this->repository->getPaginatedData($filters)
        );
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function getById(int $id)
    {
        return $this->model->findOrFail($id);
    }
    public function getAgent(Agent $agent)
    {
        return $this->remember(
            $this->byIdCacheKey($agent->id),
            fn() => $agent
        );
    }

    /* ==========================================================
     | Write Operations (Invalidate Cache)
     |========================================================== */

    public function createAgent(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['agent_id'] = $this->generateAgentId();
            $user = $this->repository->createUser($data);
            $agent = $this->repository->createAgent($user->id, $data);
             $this->repository->assignRoles($user, $data['role_id']);

            if ($this->partyService->shouldCreatePartyAccount($data)) {
                $this->partyService->createFromSourceModule('agent', $agent->load('user'));
            }

            $this->flushCache();
            return $agent;
        });
    }

    public function updateAgent(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $agent = $this->model->with('user')->findOrFail($id);
            $this->repository->updateUser($agent->user, $data, $agent);
            $agent = $this->repository->updateAgent($agent, $data);
             $this->repository->assignRoles($agent->user, $data['role_id']);
            $this->flushCache();
            return $agent;
        });
    }

    public function deleteAgent(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $agent = $this->model->with('user')->findOrFail($id);
            $agent->user?->delete();
            $agent->delete();
            $this->flushCache();
            return true;
        });
    }

    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $agents = $this->model->whereIn('id', $ids)->get();
            foreach ($agents as $agent) {
                $agent->user?->delete();
                $agent->delete();
            }
            $this->flushCache();
            return count($agents);
        });
    }

    public function getByIds(array $ids)
    {
        return $this->model
            ->whereIn('id', $ids)
            ->get(['id', 'agent_image_path']); // only needed fields
    }


    public function generateAgentId(): string
    {
        $lastAgent = $this->model->orderBy('id', 'desc')->first();
        $lastNumber = $lastAgent ? $this->extractTrailingNumber($lastAgent->agent_id) : 0;

        return 'AG'.str_pad((string) ($lastNumber + 1), 3, '0', STR_PAD_LEFT);
    }

    private function extractTrailingNumber(?string $value): int
    {
        if ($value && preg_match('/(\d+)\s*$/', trim($value), $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }

    public function getAllAgentsWithPoints()
    {
        return $this->repository->getAllAgentsWithPoints();
    }

      public function getTopAgentByPoints()
    {
        return $this->repository->getTopAgentByPoints();
    }
}
