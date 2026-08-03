<?php

namespace App\Modules\Auth\Resources;

use App\Modules\Setting\Models\Setting;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;


class AuthResource extends JsonResource
{
    public function toArray($request)
    {
        $setting = Setting::first();

        if (auth()->user()->type === 'admin') {
            $employeeImage = $setting->company_logo_url;
        } else if (auth()->user()->type === 'employee') {
            $employeeImage = $this->employee?->image_url;

        } else if (auth()->user()->type === 'agent') {
            $employeeImage = $this->agent?->image_url;

        } else if (auth()->user()->type === 'client') {
            $employeeImage = $this->client?->image_url;
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'type' => $this->type,
            'employee_image' => $employeeImage,

            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy ? $this->createdBy->name : null,
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : null,
        ];
    }
}
