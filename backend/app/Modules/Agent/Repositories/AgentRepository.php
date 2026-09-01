<?php

namespace App\Modules\Agent\Repositories;

use App\Modules\Agent\Models\Agent;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;

class AgentRepository extends BaseRepository
{
    public function __construct(Agent $model)
    {
        parent::__construct($model);
    }

    protected function baseQuery(): Builder
    {
        return parent::baseQuery()->with(['user']);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);
        $this->applyAgentFilter($query, $filters['agent_id'] ?? null);
        $this->applyStatusFilter($query, $filters['status'] ?? null);
        $this->applyHasAtsApplicationsFilter($query, $filters['has_ats_applications'] ?? null);
        $this->applyAtsApplicationsCount($query, $filters['include_ats_count'] ?? null);
        $this->applyDateFilter($query, $filters);
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $query->where(function (Builder $q) use ($search) {
            $q->where('agent_id', 'like', "%{$search}%")
                ->orWhere('nid_no', 'like', "%{$search}%")
                ->orWhereHas('user', function (Builder $q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('whatsapp_no', 'like', "%{$search}%");
                });
        });
    }

    protected function applyStatusFilter(Builder $query, $status): void
    {
        if ($status === null || $status === '') {
            return;
        }

        $query->whereHas('user', function (Builder $q) use ($status) {
            $q->where('status', $status);
        });
    }

    protected function applyAgentFilter(Builder $query, ?int $agentId): void
    {
        if (!$agentId)
            return;

        $query->where('id', $agentId);
    }

    protected function applyHasAtsApplicationsFilter(Builder $query, mixed $value): void
    {
        if (!$value) {
            return;
        }

        $query->whereHas('applications', function (Builder $applicationQuery) {
            $applicationQuery->whereRaw("UPPER(application_status) = 'ATS'");
        });
    }

    protected function applyAtsApplicationsCount(Builder $query, mixed $value): void
    {
        if (!$value) {
            return;
        }

        $query->withCount([
            'applications as ats_applications_count' => function (Builder $applicationQuery) {
                $applicationQuery->whereRaw("UPPER(application_status) = 'ATS'");
            },
        ]);
    }

    protected function applyOrder(Builder $query, array $filters): void
    {
        $query->orderByDesc('agents.agent_id');
    }

    public function createUser(array $data)
    {
        return \App\Modules\Auth\Models\User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'whatsapp_no' => $data['whatsapp_no'] ?? null,
            'password' => Hash::make($data['password']),
            'type' => 'agent',
            'status' => $data['status'] ?? 1,
        ]);
    }

    public function createAgent($userId, array $data)
    {
        $agent = Agent::create([
            'user_id' => $userId,
            'agent_id' => $data['agent_id'],
            'manager_name' => $data['manager_name'] ?? null,
            'phone2' => $data['phone2'] ?? null,
            'agent_image_path' => $data['agent_image_path'] ?? null,
            'stuff_name' => $data['stuff_name'] ?? null,
            'stuff_phone' => $data['stuff_phone'] ?? null,
            'address' => $data['address'],
            'nid_no' => $data['nid_no'] ?? null,
        ]);
        $this->trackCreateAgent($agent);

        return $agent;
    }

    /**
     * Update an existing user
     */
    public function updateUser($user, array $data, $agent)
    {
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'whatsapp_no' => $data['whatsapp_no'] ?? null,
            'status' => $data['status'] ?? $user->status,
            'type' => 'agent',
        ];

        $this->trackUpdateAgent($agent);

        // Only update password if provided
        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);
    }
    public function updateAgent($agent, $data)
    {
        $agent->update([
            'address' => $data['address'],
            'nid_no' => $data['nid_no'] ?? null,
            'agent_image_path' => $data['agent_image_path'] ?? $agent->agent_image_path,
            'phone2' => $data['phone2'] ?? null,
            'stuff_name' => $data['stuff_name'] ?? null,
            'stuff_phone' => $data['stuff_phone'] ?? null,
            'manager_name' => $data['manager_name'] ?? null,
        ]);
        return $agent;
    }

    private function trackUpdateAgent($agent)
    {
        $agent->updated_by = auth()->id();
        $agent->save();
    }

    private function trackCreateAgent($agent)
    {
        $agent->created_by = auth()->id();
        $agent->save();
    }

    public function assignRoles($user, int $roleId)
    {
        return $user->roles()->sync($roleId);
    }

        public function getAllAgentsWithPoints()
    {
        return $this->model->with('user:id,name')
            ->orderBy('points', 'desc')
            ->get();

    }

    public function getTopAgentByPoints()
    {
        return $this->model->with(['user:id,name'])
            ->orderByDesc('points')
            ->first();
    }
}
