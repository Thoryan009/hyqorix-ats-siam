<?php

namespace App\Modules\Finance\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FinanceAccountLedgerEntryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'finance_account_id' => $this->finance_account_id,
            'finance_account_transaction_id' => $this->finance_account_transaction_id,
            'finance_account_type_transaction_id' => $this->finance_account_type_transaction_id,
            'finance_bill_entry_id' => $this->finance_bill_entry_id,
            'date' => $this->entry_date?->format('Y-m-d'),
            'entry_date' => $this->entry_date?->format('Y-m-d'),
            'particular' => $this->particular,
            'voucher_no' => $this->voucher_no,
            'demand_letter' => $this->demand_letter ?? '',
            'job' => $this->job ?? '',
            'client_name' => $this->client_name ?? '',
            'dr_amount' => (float) $this->dr_amount,
            'discount' => (float) $this->discount,
            'cr_amount' => (float) $this->cr_amount,
            'payment_method' => $this->payment_method ?? '',
            'remarks' => $this->remarks ?? '',
        ];
    }
}
