<?php

namespace App\Modules\Finance\Resources;

use App\Modules\Shared\Helpers\DateTimeFormatter;
use Illuminate\Http\Resources\Json\JsonResource;

class FinanceAccountResource extends JsonResource
{
    public function toArray($request): array
    {
        $status = $this->status === 'active' ? 'Active' : 'Inactive';
        $accountType = $this->account_type
            ? ucfirst($this->account_type)
            : null;

        return [
            'id' => $this->id,
            'category' => $this->category,
            'account_name' => $this->account_name,
            'account_label' => $this->account_label,
            'account_type' => $accountType,
            'account_type_raw' => $this->account_type,
            'bank_id' => $this->bank_id,
            'bank_name' => $this->bank?->bank_name,
            'icon' => $this->icon,
            'code' => $this->code,
            'phone' => $this->phone,
            'balance' => $this->resolvedBalance(),
            'current_balance' => $this->resolvedBalance(),
            'opening_balance' => (float) $this->opening_balance,
            'has_ledger_entries' => array_key_exists('ledger_entries_count', $this->resource->getAttributes())
                ? (int) $this->ledger_entries_count > 0
                : $this->ledgerEntries()->exists(),
            'expense_head_id' => $this->expense_head_id,
            'head_id' => $this->income_head_id ?? $this->expense_head_id,
            'head_name' => $this->incomeHead?->name ?? $this->expenseHead?->name ?? $this->account_name,
            'expense_category_id' => $this->expense_category_id,
            'category_id' => $this->income_category_id ?? $this->expense_category_id,
            'category_name' => $this->incomeCategory?->name ?? $this->expenseCategory?->name,
            'income_head_id' => $this->income_head_id,
            'income_category_id' => $this->income_category_id,
            'base_price' => $this->base_price !== null ? (float) $this->base_price : null,
            'amount' => $this->ledgerAmount(),
            'entity_id' => $this->entity_id,
            'bill_agent_id' => $this->bill_agent_id,
            'metadata' => $this->metadata ?? [],
            'status' => $status,
            'status_raw' => $this->status,
            'agent_id' => $this->category === 'agent' ? $this->entity_id : null,
            'agent_code' => $this->category === 'agent' ? $this->code : null,
            'agent_name' => $this->category === 'agent' ? $this->account_name : null,
            'vendor_id' => $this->category === 'vendor' ? $this->entity_id : null,
            'vendor_code' => $this->category === 'vendor' ? $this->code : null,
            'vendor_name' => $this->category === 'vendor' ? $this->account_name : null,
            'principal_id' => $this->category === 'principal' ? $this->entity_id : null,
            'principal_code' => $this->category === 'principal' ? $this->code : null,
            'principal_name' => $this->category === 'principal' ? $this->account_name : null,
            'client_id' => $this->category === 'client' ? $this->entity_id : null,
            'client_code' => $this->category === 'client' ? $this->code : null,
            'client_name' => $this->category === 'client' ? $this->account_name : null,
            'staff_id' => $this->category === 'staff' ? $this->entity_id : null,
            'staff_code' => $this->category === 'staff' ? $this->code : null,
            'staff_name' => $this->category === 'staff' ? $this->account_name : null,
            'applicant_id' => $this->category === 'applicant' ? $this->entity_id : null,
            'applicant_code' => $this->category === 'applicant' ? $this->code : null,
            'applicant_name' => $this->category === 'applicant' ? $this->account_name : null,
            'banks_id' => $this->category === 'banks' ? $this->bank_id : null,
            'banks_code' => $this->category === 'banks' ? $this->code : null,
            'banks_name' => $this->category === 'banks' ? $this->account_name : null,
            'passport_no' => $this->category === 'applicant'
                ? ($this->applicantApplication?->passport_no
                    ?? ($this->metadata['passport_no'] ?? null))
                : null,
            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy?->name,
            'updated_by' => $this->updatedBy?->name,
        ];
    }

    private function ledgerAmount(): ?float
    {
        if (!in_array($this->category, [
            'direct_expense',
            'client_recruitment',
            'operating_expense',
            'recruitment_income',
            'client_income',
            'other_income',
            'applicant',
        ], true)) {
            return null;
        }

        // Signed ledger net (CR − DR) — same as balance so list matches ledger pages.
        return $this->ledgerNetAmount();
    }

    private function resolvedBalance(): float
    {
        // All account lists follow ledger running balance: CR − DR.
        return $this->ledgerNetAmount();
    }

    private function ledgerNetAmount(): float
    {
        if (
            !array_key_exists('ledger_dr_amount', $this->getAttributes())
            && !array_key_exists('ledger_cr_amount', $this->getAttributes())
        ) {
            $dr = (float) $this->ledgerEntries()->sum('dr_amount');
            $cr = (float) $this->ledgerEntries()->sum('cr_amount');

            return round($cr - $dr, 2);
        }

        $dr = (float) ($this->ledger_dr_amount ?? 0);
        $cr = (float) ($this->ledger_cr_amount ?? 0);

        return round($cr - $dr, 2);
    }
}
