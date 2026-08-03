<?php

namespace App\Modules\Finance\Resources;

use App\Modules\Shared\Helpers\DateTimeFormatter;
use Illuminate\Http\Resources\Json\JsonResource;

class FinanceAccountTransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'from_account_id' => $this->from_account_id,
            'to_account_id' => $this->to_account_id,
            'from_account_name' => $this->fromAccount?->account_name,
            'to_account_name' => $this->toAccount?->account_name,
            'amount' => (float) $this->amount,
            'voucher_no' => $this->voucher_no,
            'particular' => $this->particular,
            'reference_no' => $this->reference_no,
            'remarks' => $this->remarks,
            'transaction_date' => $this->transaction_date?->format('Y-m-d'),
            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
        ];
    }
}
