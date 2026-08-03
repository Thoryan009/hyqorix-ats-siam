<?php

namespace App\Modules\Finance\Resources;

use App\Modules\Shared\Helpers\DateTimeFormatter;
use Illuminate\Http\Resources\Json\JsonResource;

class FinanceAccountTypeTransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'transaction_type' => $this->transaction_type,
            'amount' => (float) $this->amount,
            'date' => $this->transaction_date?->format('Y-m-d'),
            'transaction_date' => $this->transaction_date?->format('Y-m-d'),
            'particular' => $this->particular,
            'reference_no' => $this->reference_no ?? '',
            'remarks' => $this->remarks ?? '',
            'voucher_no' => $this->voucher_no,
            'account_category' => $this->account_category ?? '',
            'main_account_type' => $this->main_account_type ?? '',
            'account_id' => $this->account_id,
            'account_label' => $this->account_label ?? '',
            'from_account_category' => $this->from_account_category ?? '',
            'from_main_account_type' => $this->from_main_account_type ?? '',
            'from_account_id' => $this->from_account_id,
            'from_account_label' => $this->from_account_label ?? '',
            'to_account_category' => $this->to_account_category ?? '',
            'to_main_account_type' => $this->to_main_account_type ?? '',
            'to_account_id' => $this->to_account_id,
            'to_account_label' => $this->to_account_label ?? '',
            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
        ];
    }
}
