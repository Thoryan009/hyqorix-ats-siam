<?php

namespace App\Modules\Application\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmbassyListItemResource extends JsonResource
{
    public function toArray($request): array
    {
        $visaProcess = $this->application?->processes
            ?->first(fn ($process) => !empty($process->data['visa_no']) || !empty($process->data['sponsor_id']));

        return [
            'id' => $this->id,
            'list_type' => $this->list_type,
            'application_id' => $this->application_id,
            'passport_no' => $this->passport_no,
            'sort_order' => $this->sort_order,
            'given_name' => $this->application?->given_name,
            'sur_name' => $this->application?->sur_name,
            'visa_no' => $visaProcess?->data['visa_no'] ?? null,
            'sponsor_id' => $visaProcess?->data['sponsor_id'] ?? null,
            'date_of_issue' => substr($visaProcess?->data['date_of_issue'] ?? '', 0, 4) ?? null, // just year ta ber korte hobe string theke
            'visit_work_for_ar' => $this->application?->embassySubmission?->visit_work_for_ar,
            'visa_profession_ar' => $this->application?->embassySubmission?->visa_profession_ar,
            'visa_profession_en' => $this->application?->embassySubmission?->visa_profession_en,
        ];
    }
}
