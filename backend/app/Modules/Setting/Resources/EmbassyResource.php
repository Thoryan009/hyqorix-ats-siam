<?php

namespace App\Modules\Setting\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;

class EmbassyResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            // 'embassy_name' => $this->embassy_name,
            // 'embassy_address' => $this->embassy_address,
            // 'embassy_company_name' => $this->embassy_company_name,
            'company_rl' => $this->company_rl,
            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy ? $this->createdBy->name : null,
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : null,
        ];
    }
}
