<?php

namespace App\Modules\Setting\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;
use Illuminate\Support\Facades\Auth;

class SettingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'software_name' => $this->software_name,
            'software_version' => $this->software_version ?: '26.1',
            'company_name' => $this->company_name,
            'company_no' => $this->company_no,
            'company_no_active' => (int) $this->company_no_active,
            'company_phone' => $this->company_phone,
            'company_email' => $this->company_email,
            'backup_email' => $this->when(Auth::check(), $this->backup_email),
            'company_address' => $this->company_address,
            'company_logo_path' => $this->company_logo_path,
            'company_logo_url' => $this->company_logo_url,
            'fav_icon_path' => $this->fav_icon_path,
            'fav_icon_url' => $this->fav_icon_url,
            'primary_color' => $this->primary_color ?: '#10b981',
            'login_background_image_path' => $this->login_background_image_path,
            'login_background_image_url' => $this->login_background_image_url,

            'embassy_name' => $this->embassy_name,
            'embassy_address' => $this->embassy_address,
            'embassy_company_name' => $this->embassy_company_name,
            'company_rl' => $this->company_rl,
            'expiry_report_notify_department_id' => $this->expiry_report_notify_department_id,
            'tasheer_appointment_email' => $this->tasheer_appointment_email,

            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy ? $this->createdBy->name : null,
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : null,
        ];
    }
}
