<?php

namespace App\Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinanceBillEntryMultiHeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:expense_categories,id'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'batch_ref' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'receipt_path' => ['nullable', 'array', 'max:10'],
            'receipt_path.*' => ['file', 'mimetypes:image/*', 'max:2048'],
            'requested_by_id' => ['nullable', 'integer'],
            'requested_by_name' => ['nullable', 'string', 'max:255'],
            'requested_by_email' => ['nullable', 'string', 'max:255'],
            'requested_by_type' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'in:submitted,pending'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.head_id' => ['required', 'integer', 'exists:expense_heads,id', 'distinct'],
            'lines.*.amount' => ['required', 'numeric', 'min:0.01'],
            'lines.*.voucher_no' => ['nullable', 'string', 'max:255'],
            'lines.*.bill_no' => ['nullable', 'string', 'max:255'],
            'lines.*.reference_no' => ['nullable', 'string', 'max:255'],
            'lines.*.particular' => ['nullable', 'string', 'max:500'],
            'lines.*.linked_account_category' => ['nullable', 'string', 'max:100'],
            'lines.*.linked_account_id' => ['nullable', 'integer'],
            'lines.*.linked_account_name' => ['nullable', 'string', 'max:255'],
            'lines.*.linked_account_type' => ['nullable', 'string', 'max:100'],
            'lines.*.expense_cost_type' => ['nullable', 'string', 'max:100'],
            'lines.*.expense_cost_account_id' => ['nullable', 'integer'],
            'lines.*.expense_cost_account_name' => ['nullable', 'string', 'max:255'],
            'lines.*.expense_cost_category_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
