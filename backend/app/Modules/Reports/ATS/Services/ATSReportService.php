<?php

namespace App\Modules\Reports\ATS\Services;

use App\Modules\Reports\ATS\Reports\{
    ActiveProcessReport,
    ExpiredProcessReport,
    ExpiringProcessReport,
    AllProcessReport
};
use Illuminate\Support\Facades\Cache;
use App\Modules\Reports\ATS\Repositories\ATSReportRepository;
use App\Modules\Reports\ATS\Resource\ATSResource;
use InvalidArgumentException;
use Illuminate\Support\Facades\Response;


class ATSReportService
{
    public function resolve(string $type)
    {
        return match ($type) {
            'active'   => app(ActiveProcessReport::class),
            'expired'  => app(ExpiredProcessReport::class),
            'expiring' => app(ExpiringProcessReport::class),
            'all' => app(AllProcessReport::class),
            default => throw new InvalidArgumentException('Invalid employee report type'),
        };
    }

    public function getAllAtsReport(array $filters)
    {
        $query = $this->getQuery($filters);
        return ATSResource::collection(
            $query->paginate(
            $filters['per_page'] ?? 10,
            ['*'],
            'page',
            $filters['page'] ?? 1
        )
        );
    }


  public function getRecentDashboardDataWithSummary()
    {
        $cacheKey = 'ats_dashboard_data';

        return Cache::remember($cacheKey, now()->addMinutes(10), function ()  {
            $repository = app(ATSReportRepository::class);

            $baseQuery = $repository->baseQuery($filters = []); // No filters for dashboard, consider all data

            $expired   = $repository->filterByDuration(clone $baseQuery, 100)->count();
            $expiring  = $repository->filterByDuration(clone $baseQuery, 50, 100)->count();
            $active    = $repository->filterByDuration(clone $baseQuery, 0, 50)->count();

            $totalProcess = $expired + $expiring + $active;

            $recentProcesses = (clone $baseQuery)
                ->latest()
                ->take(10)
                ->get();

            return (object)[
                'total_expired_process'  => $expired,
                'total_expiring_process' => $expiring,
                'total_active_process'   => $active,
                'total_process'          => $totalProcess,
                'recent_process_reports' => $recentProcesses,
            ];
        });
    }

    public function getQuery(array $filters)
    {
        $repository = app(ATSReportRepository::class);
        return $repository->baseQuery($filters);
    }



    // from chatgpt


public function exportToCSV($data)
    {
        $fileName = 'ats-report-' . now()->format('d-m-Y') . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate",
            "Expires"             => "0",
        ];

        // ✅ existing columns + new 3টা add
        $columns = [
            'Client',
            'Candidate',
            'Passport No',
            'Job Name',
            'Medical Test',
            'Police Clearance',
            'Biometric Enrollment',
            'Trade Test',

            // 🔥 NEW
            'Visa Endorsement',
            'Visa Expiry',
            'Immigration Clearance',
            'Flight Date',
            'Remarks',

            'Started At',
            'Completed At',
            'Created At',
            'Updated At',
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($data as $row) {

                // 🔥 NEW values
                $visaEndorsement = $this->getProcessDataByIdFromRow($row, 7, 'endorsment_date');
                $visaExpiry      = $this->getProcessDataByIdFromRow($row, 7, 'visa_expiry');
                $flightDate      = $this->getProcessDataByIdFromRow($row, 12, 'flight_date');
                $medicalTest      = $this->getProcessDataByIdFromRow($row, 3, 'medical_fit');
                $biometricEnrollment      = $this->getProcessDataByIdFromRow($row, 6, 'status');
                $tradeTest      = $this->getProcessDataByIdFromRow($row, 5, 'status');
                $immigrationClearance      = $this->getProcessDataByIdFromRow($row, 10, 'clearance_status');

                fputcsv($file, [
                    $row->jobList?->workOrder?->client?->user?->name,
                    $row->sur_name . ' ' . $row->given_name,
                    $row->passport_no,
                    $row->jobList?->name,
                    $medicalTest,
                    $this->getProcessStatusFromRow($row, 'police_clearance'),
                    $biometricEnrollment,
                    $tradeTest,
                    $visaEndorsement,
                    $visaExpiry,
                    $immigrationClearance,
                    $flightDate,
                    $row->currentProcess?->remarks,
                    optional($row->currentProcess)->started_at,
                    optional($row->currentProcess)->completed_at,
                    $row->created_at,
                    $row->updated_at,
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function getProcessStatusFromRow($row, $processName)
    {
        $process = $row->processes
            ->firstWhere('process.name', $processName);

        return $process?->status;
    }

    private function getProcessDataByIdFromRow($row, $processId, $key)
    {
        $process = $row->processes
            ->firstWhere('process_id', $processId);

        return data_get($process?->data, $key);
    }
}
