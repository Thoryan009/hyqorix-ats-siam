<?php

namespace App\Modules\Application\Services;

use Illuminate\Support\Facades\DB;
use App\Events\ApplicationProcessCreated;
use App\Modules\Agent\Models\Agent;
use App\Modules\Application\Services\ProcessService;
use App\Modules\Application\Models\ApplicationProcess;
use App\Modules\Application\Services\ApplicationService;
use App\Modules\Application\Contracts\JobListServiceInterface;
use App\Modules\JobList\Services\AtsService;
use App\Services\BaseCachedService;

class ApplicationProcessService extends BaseCachedService
{
    public function __construct(
        protected JobListServiceInterface $jobListService,
        protected ApplicationService $applicationService,
        protected ProcessService $processService,
        protected AtsService $atsService
    ) {
        $this->model = new ApplicationProcess();
    }


    /* ==========================================================
     | Write Operations (Invalidate Cache)
     |========================================================== */

    public function createBulkNextProcess(array $data)
    {

        $applicationIds = $data['application_ids'];
        $targetProcessId = (int) $data['next_process_id'];

        foreach ($applicationIds as $applicationId) {
            $this->createNextProcess([
                'application_id' => $applicationId,
                'next_process_id' => $targetProcessId,
                'processData' => $data['processData'] ?? []
            ]);
        }
    }


    public function createNextProcess(array $data)
    {
        // \Log::info('Creating process with data: ', $data);
        // return $data;

        $applicationId = $data['application_id'];
        $targetProcessId = (int) $data['next_process_id'];
        if (count($data['processData'] ?? []) > 0) {
            $this->updateProcess($data['processData']);
        }

        $records = DB::transaction(function () use ($applicationId, $targetProcessId) {

            $currentProcessId = $this->getCurrentProcessId($applicationId);

            if (!$this->canMoveForward($currentProcessId, $targetProcessId)) {
                return collect(); // return empty collection
            }

            $insertData = $this->prepareProcessInsertData(
                $applicationId,
                $currentProcessId,
                $targetProcessId
            );

            if (!empty($insertData)) {
                $this->model->insert($insertData);
            }

            // Fetch all newly created processes
            return $this->model
                ->where('application_id', $applicationId)
                ->whereIn('process_id', array_column($insertData, 'process_id'))
                ->get();
        });

        ApplicationProcessCreated::dispatch($records); // dispatch event with actual models
        $this->flushCache();

        return $records;
    }

    protected function getCurrentProcessId(int $applicationId): int
    {
        $lastProcess = $this->model
            ->where('application_id', $applicationId)
            ->latest('id') // safer than process_id
            ->first();

        return $lastProcess?->process_id ?? 0;
    }

    protected function canMoveForward(int $current, int $target): bool
    {
        return $target > $current;
    }

    protected function prepareProcessInsertData(
        int $applicationId,
        int $currentProcessId,
        int $targetProcessId
    ): array {
        $insertData = [];

        for ($i = $currentProcessId + 1; $i <= $targetProcessId; $i++) {

            $process = $this->processService->getById($i);

            $insertData[] = [
                'application_id' => $applicationId,
                'process_id' => $i,
                'status' => 'pending',
                'data' => json_encode([]),
                'remarks' => ucwords(str_replace('_', ' ', $process->name)) . ' Process Started.',
                'started_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ];
        }

        return $insertData;
    }

    public function updateProcess(array $data)
    {
        // \Log::info('Updating process with data: ', $data);
        // return $data;

        // if $data->status is rejected, then we need to find the agent of this application and reduce 2 point from his points
      if(  $data['status'] == 'rejected')
        {
            $applicationProcess = $this->model->findOrFail($data['application_process_id']);
            $agentId = $applicationProcess->application->agent_id;
            if($agentId)
            {
                $agent = Agent::find($agentId);
                if($agent)
                {
                    $agent->points -= 2;
                    $agent->save();
                }
            }
        }

        // if $data->status is completed and process is on_boarding, then we need to find the agent of this application and add 2 point to his points

        $applicationProcess = $this->model->findOrFail($data['application_process_id']);

        if ($data['status'] == 'completed' && $applicationProcess->process->name == 'on_boarding') {

            $applicationProcess = $this->model->findOrFail($data['application_process_id']);
            $agentId = $applicationProcess->application->agent_id;
            if ($agentId) {
                $agent = Agent::find($agentId);
                if ($agent) {
                    $agent->points += 2;
                    $agent->save();
                }
            }
        }

        $applicationProcess->update([
            'status' => $data['status'],
            'remarks' => $data['remarks'] ?? $applicationProcess->remarks,
            'data' => $data['data'] ?? null,
            'completed_at' => in_array($data['status'], ['completed', 'rejected'])
                ? now()
                : null,
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),

        ]);
        $this->flushCache();
        $this->jobListService->clearJobListCache();

        return $applicationProcess;
    }


    public function bulkOfferExtend(int $jobId, int $process_id, array $applicationIds)
    {
        $processes = [];
        $offerExtentedProcessId = $this->processService->getProcessIdByName('offer_extended');
        if ($process_id == $this->processService->getHiringListProcessId()) {
            $application_status = 'ATS';
            $applications = [];
            foreach ($applicationIds as $applicationId) {
                $applications[] = [
                    'id' => $applicationId,
                    'application_status' => $application_status,
                ];
            }
            $this->applicationService->bulkUpdate($applications);

            foreach ($applicationIds as $applicationId) {
                $processes[] = [
                    'application_id' => $applicationId,
                    'process_id' => $offerExtentedProcessId,
                    'status' => 'pending',
                    'data' => json_encode([]),
                    'remarks' => 'Offer extended Started',
                    'started_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ];
            }
            // Bulk insert processes
            DB::table('application_processes')->insert($processes);
            $this->applicationService->flushCache();
        }

        $this->flushCache();
        $this->atsService->flushCache();

        return count($processes);
    }

    protected function getByApplicationCacheKey(int $applicationId): string
    {
        return $this->getCacheTag() . "_by_application_{$applicationId}";
    }
}
