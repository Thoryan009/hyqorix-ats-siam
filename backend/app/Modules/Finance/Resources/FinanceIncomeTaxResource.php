<?php

namespace App\Modules\Finance\Resources;

use App\Modules\Shared\Helpers\DateTimeFormatter;
use Illuminate\Http\Resources\Json\JsonResource;

class FinanceIncomeTaxResource extends JsonResource
{
    public function toArray($request): array
    {
        $netProfit = round((float) $this->net_profit, 2);
        $taxAmount = round((float) $this->tax_amount, 2);
        $profitAfterTax = round($netProfit - $taxAmount, 2);

        return [
            'id' => $this->id,
            'year' => (int) $this->year,
            'tax_amount' => $taxAmount,
            'tax_rate' => $this->tax_rate !== null ? round((float) $this->tax_rate, 4) : null,
            'net_profit' => $netProfit,
            'is_profit' => $netProfit >= 0,
            'profit_after_tax' => $profitAfterTax,
            'payment_date' => $this->payment_date
                ? DateTimeFormatter::formatDate($this->payment_date)
                : null,
            'payment_date_raw' => optional($this->payment_date)?->format('Y-m-d'),
            'notes' => $this->notes,
            'status' => $this->status === 'active' ? 'Active' : 'Inactive',
            'status_raw' => $this->status,
            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy?->name,
            'updated_by' => $this->updatedBy?->name,
        ];
    }
}
