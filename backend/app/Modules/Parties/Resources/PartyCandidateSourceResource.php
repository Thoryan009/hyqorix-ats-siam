<?php

namespace App\Modules\Parties\Resources;

use App\Modules\Application\Helpers\ApplicationPresenter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartyCandidateSourceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'given_name' => $this->given_name,
            'sur_name' => $this->sur_name,
            'full_name' => ApplicationPresenter::fullName($this->given_name, $this->sur_name),
            'application_id' => $this->application_id
                ? substr((string) $this->application_id, 4)
                : null,
            'passport_no' => $this->passport_no,
        ];
    }
}
