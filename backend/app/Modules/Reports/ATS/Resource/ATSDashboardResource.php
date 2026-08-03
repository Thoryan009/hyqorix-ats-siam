<?php

namespace App\Modules\Reports\ATS\Resource;

use Illuminate\Http\Resources\Json\JsonResource;

class ATSDashboardResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'total_expiring_process'    => $this->total_expiring_process,
            'total_expired_process'     => $this->total_expired_process,
            'total_active_process'      => $this->total_active_process,
            'total_process'             => $this->total_process,
            'recent_process_reports'    => ATSResource::collection(
                $this->recent_process_reports
            ),
        ];
    }
}
