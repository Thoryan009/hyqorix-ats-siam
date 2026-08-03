<?php

namespace App\Modules\JobList\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;

class SingleAtsResource extends JsonResource
{
    public function toArray($request): array
    {
        $applications = $this->filteredApplications;
        $finalCountApplications = $this->filteredApplicationsForFinalCount;

        $agentApplicationIds = [];
        $clientApplicationIds = [];
        $user = auth()->user();
        if ($user->type == 'agent') {
            $agentApplicationIds = $user->agent ? $user->agent->applications()->pluck('id')->toArray() : [];
        } else if ($user->type == 'client') {
            $client = $this->workOrder->client;

            $clientApplicationIds = $client ? $client->workOrders()->with('jobLists.applications')->get()->pluck('jobLists.*.applications.*.id')->flatten()->toArray() : [];
        }
        $filteredApplications = $applications->filter(function ($app) use ($agentApplicationIds, $clientApplicationIds) {
            if (!empty($agentApplicationIds)) {
                return in_array($app->id, $agentApplicationIds);
            } else if (!empty($clientApplicationIds)) {
                return in_array($app->id, $clientApplicationIds);
            }
            return true;
        });

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
            'experience' => $this->experience,
            'work_order' => $this->workOrder->work_order_id ?? null,
            'client' => $this->workOrder->client->user->name ?? null,
            'applications_count' => $filteredAllApplications
                ->filter(fn ($app) => strtoupper($app->getRawOriginal('application_status') ?? '') === 'ATS')
                ->count(),
            'created_by' => $this->createdBy ? $this->createdBy->name : null,
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : null,
            'rejected_count' => $finalCountApplications
                ->filter(function ($app) {
                    return $app->currentProcess && $app->currentProcess->status == 'rejected';
                })
                ->count(),
            'declined_count' => $finalCountApplications
                ->filter(function ($app) {
                    return $app->currentProcess && $app->currentProcess->status == 'declined';
                })
                ->count(),

            'deployed_count' => $finalCountApplications
                ->filter(function ($app) {
                    return $app->currentProcess && $app->currentProcess->status == 'completed' && $app->currentProcess->process->name == 'on_boarding';
                })
                ->count(),

            'rejected_process_id' => config('app.process_rejected_id', 15),
            'declined_process_id' => config('app.process_declined_id', 16),
            'deployed_process_id' => config('app.process_deployed_id', 17),

            'all_processes' => $this->master_processes
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

            'applications' => $filteredApplications->map(function ($application) {
                return [
                    'id' => $application->id,
                    'sur_name' => $application->sur_name ?? null,
                    'given_name' => $application->given_name ?? null,
                    'full_name' => $application->given_name . ' ' . $application->sur_name,
                    'mobile' => $application->mobile ?? null,
                    'passport_no' => $application->passport_no ?? null,
                    'nid_url' => $application->nid_url,
                    'nid_link' => $application->nid_link,
                    'passport_url' => $application->passport_url,
                    'passport_link' => $application->passport_link,
                    'documents_link' => $application->documents_link ?? null,
                    'documents_url' => $application->documents_url ?? null,

                    'offer_letter_link' => $application->offer_letter_link ?? null,
                    'offer_letter_url' => $application->offer_letter_url ?? null,

                    'svp_url' => $application->svp_url,
                    'svp_link' => $application->svp_link,

                    'qvp_url' => $application->qvp_url,
                    'qvp_link' => $application->qvp_link,
                    'acknowledgment_url' => $application->acknowledgment_url,
                    'acknowledgment_link' => $application->acknowledgment_link,

                    'immigration_clearance_url' => $application->immigration_clearance_url,
                    'immigration_clearance_link' => $application->immigration_clearance_link,
                    'visa_copy_url' => $application->visa_copy_url,
                    'visa_copy_link' => $application->visa_copy_link,

                    'ticket_link' => $application->ticket_link,
                    'ticket_url' => $application->ticket_url,

                    'created_by' => $application->createdBy ? $application->createdBy->name : null,
                    'updated_by' => $application->updatedBy ? $application->updatedBy->name : null,
                    'current_process' => $application->resolved_current_process,
                    'active_process_ids' => $application->processes
                        ->pluck('process_id')
                        ->filter() // removes nulls if relation missing
                        ->values()
                        ->toArray(),
                    'processes' => $application->processes->map(function ($process) {
                        return [
                            'id' => $process->id,
                            'process_id' => $process->process_id,
                            'process' => $process->process->name,
                            'status' => $process->status,
                            'remarks' => $process->remarks ?? '',
                            'data' => $process->data,
                            'started_at' => DateTimeFormatter::formatDate($process->started_at) . ' - ' . DateTimeFormatter::formatTime($process->started_at),
                            'completed_at' => DateTimeFormatter::formatDate($process->completed_at) . ' - ' . DateTimeFormatter::formatTime($process->completed_at),
                            'created_by' => $process->createdBy ? $process->createdBy->name : null,
                            'updated_by' => $process->updatedBy ? $process->updatedBy->name : null,
                        ];
                    }),
                    'latest_transaction' =>
                    auth()->user()?->type !== 'client' && auth()->user()?->type !== 'agent' && $application->currentTransaction
                        ? [
                            'status' => $application->currentTransaction->status,
                        ]
                        : null,
                ];
            }),
        ];
    }
}
