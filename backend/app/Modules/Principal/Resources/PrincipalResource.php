<?php

namespace App\Modules\Principal\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;

class PrincipalResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'principal_id' => $this->principal_id,
            'country_id' => $this->country->id ?? null,
            'country' => $this->country->name ?? null,

            'organization_name' => $this->user->name,
            'email' => $this->user->email,
            'contact_no' => $this->user->phone,
            'whatsapp_no' => $this->user->whatsapp_no,
            'address' => $this->address,
            'send_notification' => $this->send_notification,
            'role' => count($this->user->roles) > 0 ? $this->user->roles->pluck('name')->implode(', ') : null,
            'role_id' =>  count($this->user->roles) > 0 ? $this->user->roles->first()->id : null,

            'contact_person_name' => $this->contact_person_name,
            'designation' => $this->designation,
            'contact_person_no' => $this->contact_person_no,

            'user_id' => $this->user_id,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,

            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
        ];
    }
}
