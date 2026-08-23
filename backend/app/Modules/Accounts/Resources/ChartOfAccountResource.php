<?php

namespace App\Modules\Accounts\Resources;

use App\Modules\Shared\Helpers\DateTimeFormatter;
use Illuminate\Http\Resources\Json\JsonResource;

class ChartOfAccountResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'financial_statement' => $this->financial_statement,
            'normal_balance' => ucfirst($this->normal_balance),
            'normal_balance_raw' => $this->normal_balance,
            'description' => $this->description,
            'status' => $this->status === 'active' ? 'Active' : 'Inactive',
            'status_raw' => $this->status,
            'sort_order' => (int) $this->sort_order,
            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy?->name,
            'updated_by' => $this->updatedBy?->name,
        ];
    }
}
