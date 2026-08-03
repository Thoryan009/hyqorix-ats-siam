<?php

namespace App\Modules\Reports\Tasheer\Services;

use App\Modules\Reports\Tasheer\Repositories\TasheerAppointmentReportRepository;
use App\Modules\Reports\Tasheer\Resources\TasheerAppointmentReportResource;
use App\Modules\Setting\Models\Setting;
use Illuminate\Support\Facades\Response;

class TasheerAppointmentReportService
{
    private const CSV_HEADERS = [
        'E No',
        'First Name*',
        'Second Name',
        'Last Name*',
        'Passport Number*',
        'Date of Birth*',
        'Nationality*',
        'Date of issue*',
        'Gender*',
        'Place of Issue*',
        'Expiry Date*',
        'Applicant Mobile No*',
        'Email ID*',
        // 'Job Name',
        // 'Agent Name',
        // 'Client Name',
        // 'Status',
    ];

    public function __construct(
        protected TasheerAppointmentReportRepository $repository
    ) {}

    public function getReport(array $filters = [])
    {
        $perPage = max(1, (int) ($filters['per_page'] ?? 10));
        $rows = $this->repository->baseQuery($filters)->paginate($perPage)->withQueryString();

        TasheerAppointmentReportResource::setTasheerAppointmentEmail($this->getTasheerAppointmentEmail());

        return TasheerAppointmentReportResource::collection($rows);
    }

    public function exportToCsv($filters = [])
    {
        $fileName = 'tasheer appointment form.csv';
        $email = $this->getTasheerAppointmentEmail();
        $rows = $this->repository->baseQuery($filters)->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate',
            'Expires' => '0',
        ];

        $callback = function () use ($rows, $email) {
            $file = fopen('php://output', 'w');
            fputcsv($file, self::CSV_HEADERS);

            foreach ($rows as $row) {
                fputcsv($file, $this->mapRow($row, $email));
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function getTasheerAppointmentEmail(): string
    {
        return (string) (Setting::query()->value('tasheer_appointment_email') ?? '');
    }

    private function mapRow($application, string $email): array
    {
        return [
            $application->embassySubmission?->mofa_no ?? '',
            $application->given_name ?? '',
            '',
            $application->sur_name ?? '',
            $application->passport_no ?? '',
            $application->date_of_birth ?? '',
            $application->nationality ?? '',
            $application->date_of_issue ?? '',
            $application->sex ?? '',
            'Dhaka',
            $application->date_of_expiry ?? '',
            $application->mobile ?? '',
            $email,
            // $application->jobList?->name ?? '',
            // $application->agent?->user?->name ?? '',
            // $application->jobList?->workOrder?->client?->user?->name ?? '',
            // $application->tasheer_status ?? '',
        ];
    }

    public function bulkStatusUpdate(array $ids, string $status): array
    {
        $updatedCount = $this->repository->bulkStatusUpdate($ids, $status);
        return [
            'updated_count' => $updatedCount,
            'message' => "$updatedCount records updated to status '$status'.",
        ];
    }

    public function getFilterData(): array
    {
        return $this->repository->getFilterData();
    }
}
