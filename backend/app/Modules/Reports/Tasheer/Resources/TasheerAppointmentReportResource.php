<?php

namespace App\Modules\Reports\Tasheer\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TasheerAppointmentReportResource extends JsonResource
{
    private static string $tasheerAppointmentEmail = '';

    public static function setTasheerAppointmentEmail(string $email): void
    {
        self::$tasheerAppointmentEmail = $email;
    }

    public static function getTasheerAppointmentEmail(): string
    {
        return self::$tasheerAppointmentEmail;
    }

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'e_no' => $this->embassySubmission?->mofa_no ?? '',
            'first_name' => $this->given_name ?? '',
            'second_name' => '',
            'last_name' => $this->sur_name ?? '',
            'status' => $this->tasheer_status ?? '',
            'job_name' => $this->jobList?->name ?? '',
            'agent_name' => $this->agent?->user?->name ?? '',
            'client_name' => $this->jobList?->workOrder?->client?->user?->name ?? '',
            'passport_number' => $this->passport_no ?? '',
            'date_of_birth' => $this->date_of_birth ?? '',
            'nationality' => $this->nationality ?? '',
            'date_of_issue' => $this->date_of_issue ?? '',
            'gender' => $this->sex ?? '',
            'place_of_issue' => 'Dhaka',
            'expiry_date' => $this->date_of_expiry ?? '',
            'applicant_mobile_no' => $this->mobile ?? '',
            'email_id' => self::getTasheerAppointmentEmail(),
        ];
    }
}
