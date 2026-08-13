<?php

namespace App\Modules\Agent\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;
use App\Modules\Application\Helpers\ApplicationPresenter;

class AgentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'agent_id' => $this->agent_id,
            'address' => $this->address,
            'nid_no' => $this->nid_no,
            'points' => $this->points,
            'ats_applications_count' => (int) ($this->ats_applications_count ?? 0),
            'name' => $this->user->name,
            'email' => $this->user->email,
            'phone' => $this->user->phone,
            'whatsapp_no' => $this->user->whatsapp_no,
            'phone2' => $this->phone2,
            'agent_image_url' => $this->agent_image_url,
            'stuff_name' => $this->stuff_name,
            'stuff_phone' => $this->stuff_phone,
            'manager_name' => $this->manager_name,
            'type' => $this->user->type,
            'status' => $this->user->status,
            'status_formatted' => $this->user->status == 1 ? 'Active' : 'Inactive',
            'created_by' => $this->createdBy ? $this->createdBy->name : null,
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : null,
            'role' => count($this->user->roles) > 0 ? $this->user->roles->pluck('name')->implode(', ') : null,
            'role_id' =>  count($this->user->roles) > 0 ? $this->user->roles->first()->id : null,
            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
        ];
    }
}
