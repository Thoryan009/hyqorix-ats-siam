<?php

namespace App\Modules\Parties\Resources;

use App\Modules\Shared\Helpers\DateTimeFormatter;
use Illuminate\Http\Resources\Json\JsonResource;

class PartyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'opening_debit' => number_format((float) $this->opening_debit, 2, '.', ''),
            'opening_credit' => number_format((float) $this->opening_credit, 2, '.', ''),
            'remarks' => $this->remarks,
            'status' => $this->status === 'active' ? 'Active' : 'Inactive',
            'status_raw' => $this->status,
            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy?->name,
            'updated_by' => $this->updatedBy?->name,
        ];
    }
}
