<?php

namespace App\Modules\PassportHandover\Resources;

use App\Modules\Application\Helpers\ApplicationPresenter;
use Illuminate\Http\Resources\Json\JsonResource;

class PassportHandoverSearchItemResource extends JsonResource
{
    public function toArray($request): array
    {
        $handover = $this->relationLoaded('passportHandover') ? $this->passportHandover : null;
        $application = $this->relationLoaded('application') ? $this->application : null;

        return [
            'id' => $this->id,
            'passport_handover_id' => $this->passport_handover_id,
            'handover_no' => $handover?->handover_no,
            'passport_no' => $this->passport_no,
            'candidate_name' => $application
                ? ApplicationPresenter::fullName($application->given_name, $application->sur_name)
                : null,
            'status' => $this->status,
            'handover_status' => $handover?->status,
            'taker_name' => $handover?->taker_name,
        ];
    }
}
