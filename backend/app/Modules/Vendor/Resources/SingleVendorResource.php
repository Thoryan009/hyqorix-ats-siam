<?php

namespace App\Modules\Vendor\Resources;

use App\Modules\Shared\Helpers\DateTimeFormatter;
use Illuminate\Http\Resources\Json\JsonResource;

class SingleVendorResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'vendor_id' => $this->vendor_id,
            'organization_name' => $this->organization_name,
            'name' => $this->organization_name,
            'vendor_type' => $this->vendor_type,
            'vendor_type_formatted' => $this->vendor_type
                ? ucfirst(str_replace('_', ' ', (string) $this->vendor_type))
                : null,
            'contact_person' => $this->contact_person,
            'address' => $this->address,
            'email' => $this->user->email,
            'phone' => $this->user->phone,
            'whatsapp_no' => $this->user->whatsapp_no,
            'vendor_image_url' => $this->vendor_image_url,
            'send_notification' => $this->send_notification,
            'type' => $this->user->type,
            'status' => $this->user->status,
            'status_formatted' => $this->user->status == 1 ? 'Active' : 'Inactive',
            'role' => count($this->user->roles) > 0
                ? $this->user->roles->pluck('name')->implode(', ')
                : null,
            'role_id' => count($this->user->roles) > 0
                ? $this->user->roles->first()->id
                : null,
            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy ? $this->createdBy->name : null,
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : null,
        ];
    }
}
