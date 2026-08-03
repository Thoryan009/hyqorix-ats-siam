<?php

namespace App\Modules\PassportHandover\Resources;

use App\Modules\Shared\Helpers\DateTimeFormatter;
use App\Modules\Application\Helpers\ApplicationPresenter;
use Illuminate\Http\Resources\Json\JsonResource;

class PassportHandoverItemResource extends JsonResource
{
    public function toArray($request): array
    {
        $application = $this->relationLoaded('application') ? $this->application : null;
        $handover = $this->relationLoaded('passportHandover') ? $this->passportHandover : null;

        return [
            'id' => $this->id,
            'application_id' => $this->application_id,
            'passport_no' => $this->passport_no,
            'expected_return_date' => DateTimeFormatter::formatDate($this->expected_return_date),
            'expected_return_date_raw' => $this->expected_return_date?->format('Y-m-d'),
            'taken_reason' => $this->taken_reason,
            'return_date' => DateTimeFormatter::formatDate($this->return_date),
            'return_date_raw' => $this->return_date?->format('Y-m-d'),
            'reject_date' => DateTimeFormatter::formatDate($this->reject_date),
            'reject_date_raw' => $this->reject_date?->format('Y-m-d'),
            'status' => $this->status,
            'sort_order' => $this->sort_order,
            'handed_over_by' => $this->handed_over_by,
            'handed_over_by_name' => $this->employeeName($this->handedOverBy)
                ?? $this->employeeName($handover?->handedOverBy)
                ?? $handover?->createdBy?->name,
            'collected_by' => $this->collected_by,
            'collected_by_name' => $this->employeeName($this->collectedBy)
                ?? $this->collectedByUser?->name,
            'rejected_by' => $this->rejected_by,
            'rejected_by_name' => $this->employeeName($this->rejectedBy)
                ?? $this->rejectedByUser?->name,
            'candidate_name' => $application
                ? ApplicationPresenter::fullName($application->given_name, $application->sur_name)
                : null,
        ];
    }

    private function employeeName($employee): ?string
    {
        if (!$employee) {
            return null;
        }

        return $employee->user?->name;
    }
}
