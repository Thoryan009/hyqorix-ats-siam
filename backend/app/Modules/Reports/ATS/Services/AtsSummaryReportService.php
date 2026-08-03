<?php

namespace App\Modules\Reports\ATS\Services;

use App\Modules\Reports\ATS\Repositories\AtsSummaryReportRepository;

class AtsSummaryReportService
{
    public function __construct(
        private AtsSummaryReportRepository $repository
    ) {
    }

    public function getSummary($filters): array
    {
        return $this->repository->getSummary($filters);
    }

   public function exportToCSV($filters)
{
    $summaryData = $this->getSummary($filters);

    $rows = $summaryData['rows'] ?? [];

    $headers = [
        'Client Name',
        'Job Name',
        'Hiring List',
        'Offer Extended',
        'Visa Authorization',
        'Medical Test',
        'Police Clearance',
        'Trade Test',
        'Biometric Enrollment',
        'Embassy Submission',
        'BMET Training',
        'BMET Biometric Enrollment',
        'Immigration Clearance',
        'PTA Request',
        'TRA Process',
        'On Boarding',
    ];

    $output = fopen('php://temp', 'w+');

    fputcsv($output, $headers);

    foreach ($rows as $row) {

        fputcsv($output, [
            $row['client_name'],
            $row['job_name'],
            $row['hiring_list'],
            $row['offer_extended'],
            $row['visa_authorization'],
            $row['medical_test'],
            $row['police_clearance'],
            $row['trade_test'],
            $row['biometric_enrollment'],
            $row['embassy_submission'],
            $row['bmet_training'],
            $row['bmet_biometric_enrollment'],
            $row['immigration_clearance'],
            $row['pta_request'],
            $row['tra_process'],
            $row['on_boarding'],
        ]);
    }

    rewind($output);

    $csv = stream_get_contents($output);

    fclose($output);

    return $csv;
}


}
