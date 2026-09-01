<?php

namespace App\Modules\Journals\Resources;

use App\Modules\Shared\Helpers\DateTimeFormatter;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalTransactionTypeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'sort_order' => (int) $this->sort_order,
            'status' => $this->status === 'active' ? 'Active' : 'Inactive',
            'status_raw' => $this->status,
            'subledger_required' => (bool) $this->subledger_required,
            'subledger_required_label' => $this->subledger_required ? 'Yes' : 'No',
            'demand_letter_required' => (bool) $this->demand_letter_required,
            'demand_letter_required_label' => $this->demand_letter_required ? 'Yes' : 'No',
            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy?->name,
            'updated_by' => $this->updatedBy?->name,
        ];
    }
}
