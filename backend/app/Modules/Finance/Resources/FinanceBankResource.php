<?php

namespace App\Modules\Finance\Resources;

use App\Modules\Shared\Helpers\DateTimeFormatter;
use Illuminate\Http\Resources\Json\JsonResource;

class FinanceBankResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'bank_name' => $this->bank_name,
            'swift_code' => $this->swift_code,
            'address' => $this->address,
            'branch_name' => $this->branch_name,
            'status' => $this->status === 'active' ? 'Active' : 'Inactive',
            'status_raw' => $this->status,
            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy?->name,
            'updated_by' => $this->updatedBy?->name,
        ];
    }
}
