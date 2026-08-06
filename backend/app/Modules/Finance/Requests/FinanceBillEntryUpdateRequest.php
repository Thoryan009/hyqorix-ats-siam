<?php

namespace App\Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinanceBillEntryUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'pay_amount' => ['nullable', 'numeric', 'min:0'],
            'particular' => ['nullable', 'string', 'max:500'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'voucher_no' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'approval_remarks' => ['nullable', 'string'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'payment_account_category' => ['nullable', 'string', 'max:100'],
            'payment_account_type' => ['nullable', 'string', 'max:100'],
            'payment_account_id' => ['nullable', 'integer', 'exists:finance_accounts,id'],
            'payment_account_name' => ['nullable', 'string', 'max:255'],
            'approved_by' => ['nullable', 'string', 'max:255'],
            'manual_approval_manager_id' => ['nullable', 'integer', 'exists:employees,id'],
            'manual_approval_path' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'status' => ['nullable', Rule::in(['approved', 'rejected'])],
        ];
    }
}
