<?php

namespace App\Modules\Reports\Expiry\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpiryReportResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'passport_no'           => data_get($this, 'passport_no'),
            'candidate_name'        => data_get($this, 'candidate_name'),
            'mobile'                => data_get($this, 'mobile'),
            'agent_name'            => data_get($this, 'agent_name'),
            'client_name'           => data_get($this, 'client_name'),
            'job_name'              => data_get($this, 'job_name'),
            'agent_mobile_no'       => data_get($this, 'agent_mobile_no'),
            'document'              => data_get($this, 'document'),
            'expiry_date'           => data_get($this, 'expiry_date'),
            'expiry_date_formatted' => data_get($this, 'expiry_date_formatted'),
            'days_left'             => data_get($this, 'days_left'),
            'current_process'       => data_get($this, 'current_process'),
        ];
    }
}
