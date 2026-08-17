<?php

namespace App\Modules\Finance\Resources;

use App\Modules\Shared\Helpers\DateTimeFormatter;
use Illuminate\Http\Resources\Json\JsonResource;

class FinanceBillEntryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'entry_type' => $this->entry_type ?? 'expense_bill',
            'category_id' => $this->expense_category_id,
            'category_name' => $this->entry_type === 'asset_purchase'
                ? 'Asset Purchase'
                : $this->expenseCategory?->name,
            'head_id' => $this->expense_head_id,
            'head_name' => $this->entry_type === 'asset_purchase'
                ? ($this->assetAccount?->account_name ?? '')
                : $this->expenseHead?->name,
            'is_depreciation_expense' => (bool) ($this->expenseHead?->is_depreciation_expense ?? false),
            'asset_account_id' => $this->asset_account_id,
            'asset_account_name' => $this->assetAccount?->account_name ?? '',
            'vendor_account_id' => $this->vendor_account_id,
            'vendor_account_name' => $this->vendorAccount?->account_name ?? '',
            'advance_adjustment_asset_account_id' => $this->advance_adjustment_asset_account_id,
            'advance_adjustment_asset_account_name' => $this->advanceAdjustmentAssetAccount?->account_name ?? '',
            'amount' => (float) $this->amount,
            'paid_amount' => (float) ($this->paid_amount ?? 0),
            'payable_remaining' => round(max((float) $this->amount - (float) ($this->paid_amount ?? 0), 0), 2),
            'payment_method' => $this->payment_method,
            'payment_date' => $this->payment_date?->format('Y-m-d'),
            'particular' => $this->particular,
            'reference_no' => $this->reference_no ?? '',
            'voucher_no' => $this->voucher_no,
            'batch_ref' => $this->batch_ref,
            'request_no' => $this->request_no,
            'remarks' => $this->remarks ?? '',
            'receipt_path' => $this->receipt_path,
            'receipt_url' => $this->receipt_url,
            'receipt_urls' => $this->receipt_urls,
            'status' => $this->status,
            'approval_remarks' => $this->approval_remarks ?? '',
            'linked_account_category' => $this->linked_account_category ?? '',
            'linked_account_id' => $this->linked_account_id,
            'linked_account_name' => $this->linked_account_name ?? '',
            'linked_account_type' => $this->linked_account_type ?? '',
            'expense_cost_type' => $this->expense_cost_type ?? '',
            'expense_cost_account_id' => $this->expense_cost_account_id,
            'expense_cost_account_name' => $this->expense_cost_account_name ?? '',
            'expense_cost_category_name' => $this->expense_cost_category_name ?? '',
            'application_id' => $this->application_id,
            'job_id' => $this->job_list_id,
            'candidate_name' => $this->candidate_name,
            'passport_no' => $this->passport_no,
            'application_status' => $this->application_status,
            'job_name' => $this->job_name,
            'job_code' => $this->job_code,
            'client_name' => $this->client_name,
            'demand_letter_id' => $this->work_order_id,
            'demand_letter' => $this->demand_letter,
            'demand_letter_country' => $this->demand_letter_country,
            'payment_account_category' => $this->payment_account_category ?? '',
            'payment_account_type' => $this->payment_account_type ?? '',
            'payment_account_id' => $this->payment_account_id,
            'payment_account_name' => $this->payment_account_name ?? '',
            'requested_by_id' => $this->requested_by_id,
            'requested_by_name' => $this->requested_by_name ?? '',
            'requested_by_email' => $this->requested_by_email ?? '',
            'requested_by_type' => $this->requested_by_type ?? '',
            'requested_at' => DateTimeFormatter::formatDateTime($this->requested_at),
            'manager_approved_at' => DateTimeFormatter::formatDateTime($this->manager_approved_at),
            'manager_approved_by' => $this->manager_approved_by ?? '',
            'is_manual_request' => (bool) $this->is_manual_request,
            'manual_approval_manager_id' => $this->manual_approval_manager_id,
            'manual_approval_manager_name' => $this->manual_approval_manager_name ?? '',
            'manual_approval_url' => $this->manual_approval_url,
            'approved_at' => DateTimeFormatter::formatDateTime($this->approved_at),
            'approved_by' => $this->approved_by,
            'rejected_at' => DateTimeFormatter::formatDateTime($this->rejected_at),
            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
        ];
    }
}
