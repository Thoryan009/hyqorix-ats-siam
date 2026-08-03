<?php

namespace App\Modules\Application\Resources;

use App\Modules\Application\Models\Application;
use App\Modules\Setting\Models\Setting;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;

class EmbasySubmissionResource extends JsonResource
{
    private function calculateAge(?string $dob): string
    {
        if (!$dob) {
            return '';
        }

        $birthDate = new \DateTime($dob);
        $today = new \DateTime();

        $diff = $today->diff($birthDate);

        return "{$diff->y} years {$diff->m} months {$diff->d} days";
    }
    public function toArray($request): array
    {
       $application = $this->application
    ?? Application::where('id', $this->application_id)->first();
        $setting = Setting::first();
        /*
        |--------------------------------------------------------------------------
        | Process Helpers
        |--------------------------------------------------------------------------
        */


        $visaProcess = $application?->processes
            ?->firstWhere('process_id', 2);

        $embassyProcess = $application?->processes
            ?->firstWhere('process_id', 7);

        $medicalProcess = $application?->processes
            ?->firstWhere('process_id', 3);



        $visaData = is_array($visaProcess?->data)
            ? $visaProcess->data
            : json_decode($visaProcess?->data ?? '[]', true);

        $embassyData = is_array($embassyProcess?->data)
            ? $embassyProcess->data
            : json_decode($embassyProcess?->data ?? '[]', true);
        $medicalData = is_array($medicalProcess?->data)
            ? $medicalProcess->data
            : json_decode($medicalProcess?->data ?? '[]', true);

        return [
            'id' => $this->id ?? null,

            /*
            |--------------------------------------------------------------------------
            | Application Info
            |--------------------------------------------------------------------------
            */

            'application_id' => $this->application_id ?? null,

            /*
            |--------------------------------------------------------------------------
            | Personal Information
            |--------------------------------------------------------------------------
            */

            'full_name_with_father_name' => trim(
                ($application?->given_name ?? '') . ' ' .
                ($application?->sur_name ?? '') . ' S/O. '.
                ($application?->father_name ?? '')
            ),

            'mother_name' => $application?->mother_name,
            'father_name' => $application?->father_name,

            'date_of_birth' => $application?->date_of_birth,
            'place_of_birth' => $application?->place_of_birth,

            'present_nationality' => $application?->nationality,

            'sex' => $application?->sex,
            'calculateAge' => $this->calculateAge($application?->date_of_birth ?? null),
            'marital_status' => ucfirst($application?->marital_status ?? ''),

            /*
            |--------------------------------------------------------------------------
            | Embassy Submission
            |--------------------------------------------------------------------------
            */

            'religion' => ($this->religion ?? null)
            ? (($this->religion ?? null) == 'muslim'
                ? 'Muslim'
                : 'Non Muslim')
            : null,
            // 'place_of_issue' => $this->place_of_issue,

            /*
            |--------------------------------------------------------------------------
            | Qualification & Profession
            |--------------------------------------------------------------------------
            */

            // 'qualification' => $application?->qualification?->name,

            'profession' => $this->visa_profession_en ?? null,
            'profession_arabic' => $this->visa_profession_ar ?? null,

            /*
            |--------------------------------------------------------------------------
            | Contact Information
            |--------------------------------------------------------------------------
            */


            'embassy_name' => $setting?->embassy_name,
            'embassy_company_name' => $setting?->embassy_company_name,
            
            'embassy_company_address' => $setting?->embassy_address,
            'company_address' => $setting?->company_address,
            'company_rl' => $setting?->company_rl,

            /*
            |--------------------------------------------------------------------------
            | Purpose Of Travel
            |--------------------------------------------------------------------------
            */

            'purpose_of_travel' => 'work',

            'visit_work_for_arabic' => $this->visit_work_for_ar ?? null,
            'visit_work_for_english' => $this->visit_work_for_en ?? null,
            'mofa_no' => $this->mofa_no ?? null,
            'police_clearance_no' => $this->police_clearance_no ?? null,
            'alwakala_no' => $this->alwakala_no ?? null,

            /*
            |--------------------------------------------------------------------------
            | Passport Information
            |--------------------------------------------------------------------------
            */

            'passport_date_of_issue' => $application?->date_of_issue,
            'passport_date_of_expiry' => $application?->date_of_expiry,
            'passport_no' => $application?->passport_no,

            /*
            |--------------------------------------------------------------------------
            | Visa Process Data
            |--------------------------------------------------------------------------
            */

            'visa_no' => $visaData['visa_no'] ?? $this->visa_no ?? null,
            'authorization' => $visaData['sponsor_id'] ?? null,
            'visa_issue_date' => $visaData['date_of_issue'] ?? null,

            'medical_status' => ucfirst($medicalData['medical_fit'] ?? null),

            /*
            |--------------------------------------------------------------------------
            | Embassy Submission Process
            |--------------------------------------------------------------------------
            */

            'visa_expiry_date' => $embassyData['visa_expiry'] ?? null,
            'passport_collection_date' => $embassyData['collection_date'] ?? null,




            /*
            |--------------------------------------------------------------------------
            | Applicant Information
            |--------------------------------------------------------------------------
            */

            'applicant_name' => trim(
                ($application?->given_name ?? '') . ' ' .
                ($application?->sur_name ?? '')
            ),

            'created_at' => DateTimeFormatter::formatDateTime($this->created_at ?? null)  ?? null,

            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at ?? null) ?? null,
        ];
    }
}
