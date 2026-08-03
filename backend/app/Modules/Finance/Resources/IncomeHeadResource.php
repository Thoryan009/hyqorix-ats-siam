<?php

namespace App\Modules\Finance\Resources;

use App\Modules\Shared\Helpers\DateTimeFormatter;
use Illuminate\Http\Resources\Json\JsonResource;

class IncomeHeadResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->income_category_id,
            'income_category_id' => $this->income_category_id,
            'category_name' => $this->incomeCategory?->name,
            'category_code' => $this->incomeCategory?->code,
            'name' => $this->name,
            'base_price' => (float) $this->base_price,
            'linked_accounts' => collect($this->linked_accounts ?? [])
                ->map(fn ($link) => [
                    'account_category' => $link['account_category'] ?? '',
                ])
                ->filter(fn ($link) => $link['account_category'] !== '')
                ->values()
                ->all(),
            'is_bills_payable_link' => (bool) $this->is_bills_payable_link,
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
