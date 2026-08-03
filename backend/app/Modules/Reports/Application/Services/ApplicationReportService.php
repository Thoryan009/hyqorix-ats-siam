<?php

namespace App\Modules\Reports\Application\Services;
use App\Modules\Reports\Application\Reports\{
    ApplicationReport,

};
use App\Modules\Reports\Application\Repositories\ApplicationReportRepository;
use App\Modules\Reports\Application\Resources\ApplicationReportResource;
use InvalidArgumentException;
use App\Modules\Application\Models\ApplicationProcess;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Cache;

class ApplicationReportService
{
    public function resolve(string $type)
    {
        return match ($type) {
            'application' => app(ApplicationReport::class),
            'client' => app(ApplicationReport::class),
            'agent' => app(ApplicationReport::class),
            'principal' => app(ApplicationReport::class),
            default     => throw new InvalidArgumentException('Invalid application report type'),
        };
    }

    public function getAllApplication(array $filters)
    {
        $repository = app(ApplicationReportRepository::class);

        return ApplicationReportResource::collection(
            $repository->baseQuery($filters)->get()
        );
    }

    public function getRecentDashboardDataWithSummary()
    {
         $cacheKey = 'application_dashboard_data';

         return Cache::remember($cacheKey, now()->addMinutes(10), function ()  {
            $repository = app(ApplicationReportRepository::class);

            $agent = auth()->user()->agent;
            $client = auth()->user()->client;
            $principal = auth()->user()->principal;

            $baseQuery = $repository->baseQuery($filters = []); // No filters for dashboard, consider all data

            $totalApplications = $baseQuery->count();
            $totalApplicationProcess = ApplicationProcess::count();
            $recentApplications = $baseQuery->latest()->take(10)->get();

            return [
                'total_applications' => $totalApplications,
                'total_application_process' => $totalApplicationProcess,
                'recent_applications' => ($agent || $client || $principal) ? [] : ApplicationReportResource::collection($recentApplications),
            ];
         });
    }

    public function getQuery(array $filters)
    {
        $repository = app(ApplicationReportRepository::class);
        return $repository->baseQuery($filters);
    }

    public function exportToCSV($data)
{
    $fileName = 'application-report-' . now()->format('d-m-Y') . '.csv';

    $headers = [
        "Content-type"        => "text/csv",
        "Content-Disposition" => "attachment; filename=$fileName",
        "Pragma"              => "no-cache",
        "Cache-Control"       => "must-revalidate",
        "Expires"             => "0",
    ];

    $columns = [
        'Job Code',
        'Job Name',
        'Name',
        'Passport',
        'Mobile',
        'Sex',
        'Country',
        'Demand Letter',
        'Client',
        'Agent',
        'Principal',
        'Process Days',
        'Total Process Days',
        'Status',
        'Remarks',
    ];

    $callback = function () use ($data, $columns) {

        $file = fopen('php://output', 'w');

        fputcsv($file, $columns);

        foreach ($data as $row) {

            fputcsv($file, [
                $row->jobList?->job_code,
                $row->jobList?->name,

                trim(
                    ($row->sur_name ?? '') . ' ' .
                    ($row->given_name ?? '')
                ),

                $row->passport_no,
                $row->mobile,
                $row->sex,

                $row->jobList?->workOrder?->client?->country?->name,

                $row->jobList?->workOrder?->work_order_id,

                $row->jobList?->workOrder?->client?->user?->name,

                $row->agent?->user?->name,

                $row->jobList?->principal?->user?->name,

                $row->currentProcessDays() ?? null,
                $row->totalProcessDays() ?? null,

                $this->getStatusFromRow($row),

                $row->currentProcess?->remarks,
            ]);
        }

        fclose($file);
    };

    return Response::stream($callback, 200, $headers);
}

    public function getStatusFromRow($row)
    {
             $status = "";
        if ($row->current_process_name && $row->current_process_name !== 'N/A') {

            if ($row->currentProcess?->process?->name === 'on_boarding' && $row->currentProcess?->status === 'completed') {
                $status = 'deployed';
            }else if($row->currentProcess?->status == 'rejected'){
                $status = 'rejected';
             } else if($row->currentProcess?->status == 'declined'){
                $status = 'declined';

            } else {
                $status = $row->current_process_name;
            }
        } else {
            $status = $row->application_status ?? 'hiring_list';
        }
        return $status;
    }
}


