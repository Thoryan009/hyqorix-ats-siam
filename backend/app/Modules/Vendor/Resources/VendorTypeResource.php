<?php

namespace App\Modules\Vendor\Resources;

use App\Modules\Shared\Helpers\DateTimeFormatter;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorTypeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'status' => $this->status,
            'status_formatted' => $this->status === 'active' ? 'Active' : 'Inactive',
            'sort_order' => $this->sort_order,
            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy ? $this->createdBy->name : null,
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : null,
        ];
    }
}
