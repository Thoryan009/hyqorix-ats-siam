<?php

namespace App\Modules\__MODEL__\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;

class __MODEL__Resource extends JsonResource
{
   public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'software_name'     => $this->software_name,
            'company_name'      => $this->company_name,
            'company_phone'     => $this->company_phone,
            'company_email'     => $this->company_email,
            'company_address'   => $this->company_address,
            'company_logo_path' => $this->company_logo_path,
            'company_logo_url'  => $this->company_logo_url,
            'created_at'        => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at'        => DateTimeFormatter::formatDateTime($this->updated_at),
        ];
    }
}
