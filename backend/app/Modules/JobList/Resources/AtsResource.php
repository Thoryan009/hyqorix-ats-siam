<?php

namespace App\Modules\JobList\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;

class AtsResource extends JsonResource
{
    public function toArray($request): array
    {

        $applications = $this->filteredApplications;

        $agentApplicationIds = [];
        $clientApplicationIds = [];

        $user = auth()->user();
        if ($user->type == 'agent') {
            $agentApplicationIds = $user->agent ? $user->agent->applications()->pluck('id')->toArray() : [];
        } else if ($user->type == 'client') {

            $client = $this->workOrder->client;

            $clientApplicationIds = $client ? $client->workOrders()->with('jobLists.applications')->get()->pluck('jobLists.*.applications.*.id')->flatten()->toArray() : [];
        }

        $allApplications = $this->applications;
        $filteredAllApplications = $allApplications->filter(function ($app) use ($agentApplicationIds, $clientApplicationIds) {
            if (!empty($agentApplicationIds)) {
                return in_array($app->id, $agentApplicationIds);
            } else if (!empty($clientApplicationIds)) {
                return in_array($app->id, $clientApplicationIds);
            }
            return true;
        });

        return [
            'id' => $this->id,
            'name' => $this->name,
            'job_code' => $this->job_code,
            'vacancy' => $this->vacancy,
            'experience' => $this->experience,
            'salary' => $this->salary,
            'work_order' => $this->workOrder->work_order_id ?? null,
            'client' => $this->workOrder->client->user->name ?? null,
            'applications_count' => $filteredAllApplications
                ->filter(fn ($app) => strtoupper($app->getRawOriginal('application_status') ?? '') === 'ATS')
                ->count(),
            'applications_process_count' => $this->master_processes
                ->map(function ($process) use ($filteredAllApplications) {
                    return [
                        'id' => $process['id'],
                        'name' => $process['name'] === 'biometric_enrollment' ? 'biometric_enrollm' : ($process['name'] === 'bmet_biometric_enrollment' ? 'bmet_biometric_enrollm' : $process['name']),

                        'count' => $filteredAllApplications
                            ->filter(function ($app) use ($process) {
                                $latestProcess = $app->processes->sortByDesc('id')->first();

                                return $latestProcess
                                    && $latestProcess->process_id == $process['id']
                                    && $latestProcess->status != 'rejected'
                                    && $latestProcess->status != 'declined'
                                    && !($latestProcess->process->name == 'on_boarding' && $latestProcess->status == 'completed');
                            })
                            ->count(),
                    ];
                })
                ->values(),


            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy ? $this->createdBy->name : null,
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : null,
        ];
    }
}
