<?php

namespace App\Modules\Employee\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;

class DesignationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'employees_count' => $this->employees->count(),

            'employees' => $this->employees->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'user_id' => $employee->user->id,
                    'name' => $employee->user->name,
                    'email' => $employee->user->email,
                    'phone' => $employee->user->phone,
                    'username' => $employee->username,
                    'image_url' => $employee->image_url ? $employee->image_url : null,
                    'status' => $employee->user->status,
                ];
            }),
            'created_at'  => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at'  => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy ? $this->createdBy->name : null,
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : null,

        ];
    }
}
