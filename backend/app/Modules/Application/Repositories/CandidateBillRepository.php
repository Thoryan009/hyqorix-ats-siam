<?php

namespace App\Modules\Application\Repositories;

use Illuminate\Database\Eloquent\Builder;
use App\Modules\Application\Models\Transaction;
use Illuminate\Support\Facades\Hash;
use App\Repositories\BaseRepository;
use App\Modules\JobList\Helpers\JobListPayerHelper;

class CandidateBillRepository   extends BaseRepository
{
    public function __construct(Transaction $model)
    {
        $this->model = $model;
    }

    protected function baseQuery(): Builder
    {
        return $this->model->newQuery()
             ->with('application.jobList')
            ->where('status', 'bill-generated')
            ->whereHas(
                'application',
                fn ($q) => JobListPayerHelper::scopeWhereApplicationResponsible($q, JobListPayerHelper::CANDIDATE)
            )
            ->whereHas('billTransactions', function ($q) {
                $q->select('bill_no')
                    ->groupBy('bill_no')
                    ->havingRaw('COUNT(*) = 1')
                    ->havingRaw("MAX(status) = 'bill-generated'");
            });
    }

    public function applyFilters(Builder $query, array $filters): void
    {
        $this->applyApplicationSearch($query, $filters['search'] ?? null);
        $this->applyCandidateBillFilter($query, $filters['job_id'] ?? null);
        $this->applyDateFilter($query, $filters);
    }

    protected function applyApplicationSearch(
        Builder $query,
        ?string $search
    ): void {
        if (!$search) {
            return;
        }
        $query->whereHas('application', function ($q) use ($search) {
            $q->where(function ($innerQ) use ($search) {
                foreach ($this->applicationSearchFields as $field) {
                    $innerQ->orWhere($field, 'like', "%{$search}%");
                }
            });
        });
    }

    protected array $applicationSearchFields = [
        'sur_name',
        'given_name',
        'email',
        'mobile',
        'application_id',
    ];

    /**
     * Optional filter for Job_id
     */
    protected function applyCandidateBillFilter(Builder $query, ?int $jobId): void
    {
        if (!$jobId) {
            return;
        }

        $query->whereHas('application.jobList', function ($q) use ($jobId) {
            $q->where('id', $jobId);
        });
    }


    /* ================= Helper Methods ================= */

    public function createUser(array $data)
    {
        return \App\Modules\Auth\Models\User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'type'     => $data['type'],
        ]);
    }

    /**
     * Update an existing user
     */
    public function updateUser($user, array $data)
    {
        $updateData = [
            'name'  => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ];

        // Only update password if provided
        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);
    }
}
