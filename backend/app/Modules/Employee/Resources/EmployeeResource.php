<?php

namespace App\Modules\Employee\Resources;

use App\Modules\CRM\Resources\TaskResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;
use App\Modules\System\Resources\ActivityLogResource;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'user_id'    => $this->user->id,
            'name'  => $this->user->name,
            'username' => $this->username,
            'email' => $this->user->email,
            'points' => $this->points,
            'phone' => $this->user->phone,
            'whatsapp_no' => $this->user->whatsapp_no,
            'send_credentials' => $this->send_credentials,
            'show_ats_summary' => $this->show_ats_summary,
            'manager_approval' => (int) ($this->manager_approval ?? 0),
            'roles' => $this->user->roles->pluck('name')->implode(', '),
            'role_ids' => $this->user->roles->pluck('id'), // for edit form to show selected roles
            'departments' => $this->departments->pluck('name')->implode(', '),
            'department_ids' => $this->departments->pluck('id'), // for edit form to show selected departments
            'image_url' => $this->image_url ? $this->image_url : null,

            'designation_id'   => $this->designation->id,
            'designation' => $this->designation->name,
            'designation_description' => $this->designation->description,
            'status' => $this->user->status,
            'status_formatted' => $this->user->status == 1 ? 'Active' : 'Inactive',


            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy ? $this->createdBy->name : null,
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : null,
        ];
    }
}
