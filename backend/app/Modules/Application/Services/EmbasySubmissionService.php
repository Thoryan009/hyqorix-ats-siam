<?php

namespace App\Modules\Application\Services;

use App\Modules\Application\Models\Application;
use App\Modules\Application\Models\EmbasySubmission;
use App\Services\BaseCachedService;
use App\Modules\Application\Repositories\EmbasySubmissionRepository;

class EmbasySubmissionService extends BaseCachedService
{
    public function __construct(protected EmbasySubmissionRepository $repository)
    {
        parent::__construct(new EmbasySubmission());
    }

    /* ==========================================================
     | Read Operations (Cached)
     |========================================================== */

    public function getPaginatedDataWithCache(array $filters = [])
    {
        return $this->remember($this->filtersCacheKey($filters), fn() => $this->repository->getPaginatedData($filters));
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function getEmbasySubmission(EmbasySubmission $embasySubmission)
    {
        return $this->remember($this->byIdCacheKey($embasySubmission->id), fn() => $embasySubmission);
    }

    public function upsertEmbassySubmission(int $applicationId, array $data)
    {
        return $this->mutate(function () use ($applicationId, $data) {
            return $this->model->updateOrCreate(
                [
                    'application_id' => $applicationId,
                ],
                $data,
            );
        });
    }

    public function getMofaInformations(int $applicationId)
    {
        $application = Application::findOrFail($applicationId);

        $mofaInformations = $this->model->where('application_id', $applicationId)->get();

        return [
            'application' => $application,
            'mofaInformations' => $mofaInformations,
        ];
    }

    public function deleteEmbassySubmission(int $applicationId): bool
    {
        return $this->mutate(function () use ($applicationId) {
            $embassySubmission = $this->model->where('application_id', $applicationId)->first();

            if (!$embassySubmission) {
                return false;
            }

            return $embassySubmission->delete();
        });
    }

    public function bulkStatusUpdate(array $ids, $status): void
    {
        $this->mutate(function () use ($ids, $status) {

            foreach ($ids as $applicationId) {

                $this->model->updateOrCreate(
                    [
                        'application_id' => $applicationId,
                    ],
                    [
                        'ksa_visa_status' => $status,
                    ]
                );
            }
        });
    }
}
