<?php

namespace App\Modules\Finance\Resources;

use App\Modules\Shared\Helpers\DateTimeFormatter;
use Illuminate\Http\Resources\Json\JsonResource;

class FinanceIncomeCollectionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->income_category_id,
            'income_category_id' => $this->income_category_id,
            'category_name' => $this->incomeCategory?->name,
            'category_code' => $this->incomeCategory?->code,
            'head_id' => $this->income_head_id,
            'income_head_id' => $this->income_head_id,
            'head_name' => $this->incomeHead?->name,
            'amount' => (float) $this->amount,
            'payment_method' => $this->payment_method,
            'collection_date' => DateTimeFormatter::formatDate($this->collection_date),
            'collection_date_raw' => optional($this->collection_date)?->format('Y-m-d'),
            'particular' => $this->particular,
            'reference_no' => $this->reference_no,
            'voucher_no' => $this->voucher_no,
            'remarks' => $this->remarks,
            'status' => $this->status,
            'job_list_id' => $this->job_list_id,
            'job_code' => $this->job_code,
            'job_title' => $this->job_title,
            'client_name' => $this->client_name,
            'candidates' => $this->candidates ?? [],
            'linked_account_category' => $this->linked_account_category,
            'linked_account_id' => $this->linked_account_id,
            'linked_account_name' => $this->linked_account_name,
            'linked_account_type' => $this->linked_account_type,
            'receive_account_id' => $this->receive_account_id,
            'receive_account_name' => $this->receive_account_name,
            'receive_account_type' => $this->receive_account_type,
            'collected_by_name' => $this->collected_by_name,
            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
        ];
    }
}
