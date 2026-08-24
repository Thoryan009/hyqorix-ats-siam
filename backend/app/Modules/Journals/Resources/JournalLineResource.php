<?php

namespace App\Modules\Journals\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class JournalLineResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'account_code' => $this->account?->code,
            'account_name' => $this->account?->name,
            'sub_ledger' => $this->sub_ledger,
            'cost_type' => $this->cost_type,
            'debit' => number_format((float) $this->debit, 2, '.', ''),
            'credit' => number_format((float) $this->credit, 2, '.', ''),
            'sort_order' => (int) $this->sort_order,
        ];
    }
}
