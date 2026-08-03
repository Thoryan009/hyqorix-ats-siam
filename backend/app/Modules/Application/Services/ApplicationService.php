<?php

namespace App\Modules\Application\Services;

use App\Modules\Application\Models\Application;
use App\Modules\Application\Repositories\ApplicationRepository;
use App\Modules\Application\Repositories\ProcessRepository;
use App\Modules\Application\Services\ProcessService;
use App\Modules\Finance\Services\FinanceAccountService;
use App\Services\BaseCachedService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ApplicationService extends BaseCachedService
{
    // protected $model;

    public function __construct(
        private ApplicationRepository $repository,
        private ProcessService $processService,
        private ProcessRepository $processRepository,
        private FinanceAccountService $financeAccountService,
    ) {
        parent::__construct(new Application());
    }

    /* ==========================================================
     | Read Operations (Cached)
     |========================================================== */

    public function getPaginatedDataWithCache(array $filters = [])
    {
        return $this->remember($this->filtersCacheKey($filters), fn() => $this->repository->getPaginatedData($filters));
    }

    // getHiringListWithCache

    public function getById(int $id)
    {
        return $this->remember($this->byIdCacheKey($id), fn() => $this->model->with('experiences')->findOrFail($id));
    }

    public function getApplicationsWithSearch(?string $search = null)
    {
        return $this->model->when($search, fn($query) => $this->repository->applySearch($query, $search))->orderByDesc('id')->get();
    }

    public function getAllProcesses()
    {
        return Cache::tags($this->getCacheTag())->remember($this->getApplicationProcessesCacheKey(), $this->getCacheTtl(), function () {
            $processes = $this->processRepository->getProcessList();

            return $processes->prepend([
                'id' => $this->processService->getHiringListProcessId(),
                'name' => 'hiring_list',
            ]);
        });
    }

    public function getByPassportAndJobId(string $passportNumber, int $jobId)
    {
        return $this->model->where('passport_no', $passportNumber)->where('job_list_id', $jobId)->first();
    }

    /* ==========================================================
     | Write Operations (Invalidate Cache)
     |========================================================== */

    public function create(array $data)
    {
        \Log::info('Creating application', $data);
        $data['application_id'] = $this->generateApplicationId();
        $record = $this->mutate(fn() => $this->model->create($data));
        $this->financeAccountService->ensureApplicantAccount($record);

        return $record;
    }

    public function bulkUpload(array $applications, int $job_id, int $agent_id): array
    {
        if (!$this->isValidBulkInput($applications)) {
            return $this->invalidInputResponse();
        }

        $existingMap = $this->buildExistingPassportMap($applications, $job_id, $agent_id);

        [$validatedData, $errors] = $this->processApplications($applications, $job_id, $agent_id, $existingMap);

        if (empty($validatedData)) {
            return $this->noValidRecordsResponse($applications, $errors);
        }

        $this->insertApplications($validatedData);

        return $this->successResponse($applications, $validatedData, $errors);
    }

    private function isValidBulkInput(array $applications): bool
    {
        return !empty($applications) && is_array($applications);
    }

    private function insertApplications(array $validatedData): void
    {
        DB::transaction(function () use ($validatedData) {
            $this->bulkInsert($validatedData);
        });
    }

    private function successResponse(array $applications, array $validatedData, array $errors): array
    {
        return [
            'success' => true,
            'message' => 'Bulk upload completed.',
            'summary' => [
                'total' => count($applications),
                'success' => count($validatedData),
                'failed' => count($errors),
            ],
            'errors' => $errors,
        ];
    }

    private function invalidInputResponse(): array
    {
        return [
            'success' => false,
            'message' => 'No application data provided.',
        ];
    }

    private function buildExistingPassportMap(array $applications, int $job_id, int $agent_id): array
    {
        $passportNumbers = collect($applications)->pluck('passport_no')->filter()->unique()->values()->toArray();

        $existingPassports = $this->getExistingPassports($passportNumbers, $job_id, $agent_id);

        return array_flip($existingPassports);
    }

    private function processApplications(array $applications, int $job_id, int $agent_id, array $existingMap): array
    {
        $validatedData = [];
        $errors = [];

        foreach ($applications as $index => $appData) {
            try {
                $cleanData = $this->prepareApplicationData($appData);

                if ($this->isDuplicate($cleanData, $existingMap)) {
                    continue;
                }

                $this->validateRequiredFields($cleanData);

                $validatedData[] = $this->finalizeApplicationData($cleanData, $job_id, $agent_id);
            } catch (\Throwable $e) {
                $errors[] = $this->formatError($index, $appData, $e);
            }
        }

        return [$validatedData, $errors];
    }

    private function isDuplicate(array $data, array $existingMap): bool
    {
        return !empty($data['passport_no']) && isset($existingMap[$data['passport_no']]);
    }

    private function finalizeApplicationData(array $data, int $job_id, int $agent_id): array
    {
        $timestamp = now();

        $data['job_list_id'] = $job_id;
        $data['agent_id'] = $agent_id;
        $data['applied_through'] = 'agent';
        $data['payment_responsibility'] = json_encode(['agent']);
        $data['created_at'] = $timestamp;
        $data['updated_at'] = $timestamp;
        $data['created_by'] = auth()->id();

        return $data;
    }

    private function formatError(int $index, array $appData, \Throwable $e): array
    {
        return [
            'row' => $index + 1,
            'application_id' => $appData['application_id'] ?? 'N/A',
            'error' => $e->getMessage(),
        ];
    }

    private function getExistingPassports(array $passportNumbers, int $job_id, int $agent_id): array
    {
        if (empty($passportNumbers)) {
            return [];
        }

        return Application::where('job_list_id', $job_id)->where('agent_id', $agent_id)->whereIn('passport_no', $passportNumbers)->pluck('passport_no')->toArray();
    }

    private function noValidRecordsResponse(array $applications, array $errors): array
    {
        return [
            'success' => false,
            'message' => 'There are no valid records to upload.',
            'summary' => [
                'total' => count($applications),
                'success' => 0,
                'failed' => count($errors),
            ],
            'errors' => $errors,
        ];
    }

    public function bulkInsert(array $dataArray)
    {
        \Log::info('Inserting applications: ' . json_encode($dataArray));
        return $this->mutate(fn() => $this->model->insert($dataArray));
    }

    private function prepareApplicationData(array $data): array
    {
        unset($data['sl']);

        foreach ($data as $key => $value) {
            if ($value === 'null' || $value === '' || $value === 'NULL') {
                $data[$key] = null;
            }
        }

        return $data;
    }
    private function validateRequiredFields(array $data): void
    {
        $requiredFields = [
            'sur_name' => 'Surname',
            'given_name' => 'Given Name',
        ];

        foreach ($requiredFields as $field => $label) {
            if (empty($data[$field])) {
                throw new \Exception("Missing required field: {$label}");
            }
        }
    }

    public function update(int $id, array $data)
    {
        $record = $this->mutate(function () use ($id, $data) {
            $application = $this->model->findOrFail($id);
            $application->update($data);

            return $application->fresh() ?? $application;
        });

        // Use validated update payload so edit → Direct Candidate + Candidate
        // always creates an applicant account when one does not exist yet.
        $this->financeAccountService->ensureApplicantAccount(
            $record,
            $data['applied_through'] ?? null,
            $data['payment_responsibility'] ?? null,
        );

        return $record;
    }

    public function delete(int $id): bool
    {
        return $this->mutate(fn() => $this->model->findOrFail($id)->delete());
    }

    public function bulkDelete(array $ids): int
    {
        return $this->mutate(fn() => $this->model->whereIn('id', $ids)->delete());
    }
    public function updateApplicaitionStatusStartDate(array $ids): int
    {
        $currentDate = now();

        return $this->mutate(fn() => $this->model->whereIn('id', $ids)->update(['application_status_start_date' => $currentDate]));

    }

    public function bulkStatusUpdate(array $ids, string $status): int
    {
        if($status === 'hiring_list') {
            foreach ($ids as $id) {
                $application = Application::find($id);
                if($application->application_status !== 'hiring_list') {
                    $agent = $application->agent;
                    if ($agent) {
                        $agent->points += 1;
                        $agent->save();
                    }
                }
            }
        }
        return $this->mutate(fn() => $this->model->whereIn('id', $ids)->update(['application_status' => $status]));
    }

    public function bulkUpdate(array $applications): int
    {
        $updatedCount = 0;

        foreach ($applications as $appData) {
            if (isset($appData['id'])) {
                $updated = $this->update($appData['id'], ['application_status' => $appData['application_status']]);
                if ($updated) {
                    $updatedCount++;
                }
            }
        }

        return $updatedCount;
    }

    /* ==========================================================
     | Cache Helpers
     |========================================================== */

    public function generateApplicationId(): string
    {
        $lastApplication = $this->model->orderBy('id', 'desc')->first();
        $lastNumber = $lastApplication ? (int) str_replace('APP-', '', $lastApplication->application_id) : 0;
        $newNumber = $lastNumber + 1;
        return 'APP-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    protected function getApplicationJobListsCacheKey(): string
    {
        return $this->getCacheTag() . '_job_lists';
    }
    protected function getApplicationProcessesCacheKey(): string
    {
        return $this->getCacheTag() . '_processes';
    }
}
