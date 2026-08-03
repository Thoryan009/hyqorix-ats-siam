<?php

namespace App\Modules\Application\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmbassyHintResource extends JsonResource
{
    public function toArray($request): array
    {
        // Find visa process data
        $visaProcess = $this->processes
            ->first(fn($process) => !empty($process->data['visa_no']) || !empty($process->data['sponsor_id']));

        return [
            'id' => $this->id,
            'passport_no' => $this->passport_no,
            'given_name' => $this->given_name,
            'sur_name' => $this->sur_name,
            'visa_no' => $visaProcess?->data['visa_no'] ?? null,
            'sponsor_id' => $visaProcess?->data['sponsor_id'] ?? null,
            'visa_profession_ar' => $this->embassySubmission?->visa_profession_ar ?? null,
        ];
    }
}
