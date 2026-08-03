<?php

namespace App\Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinanceBillEntryBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:expense_categories,id'],
            'head_id' => ['required', 'integer', 'exists:expense_heads,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'particular' => ['nullable', 'string', 'max:500'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'batch_ref' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'receipt_path' => ['nullable', 'array', 'max:10'],
            'receipt_path.*' => ['file', 'mimetypes:image/*', 'max:2048'],
            'job_id' => ['required', 'integer', 'exists:job_lists,id'],
            'application_ids' => ['required', 'array', 'min:1'],
            'application_ids.*' => ['integer', 'exists:applications,id'],
            'linked_account_category' => ['nullable', 'string', 'max:100'],
            'linked_account_id' => ['nullable', 'integer'],
            'linked_account_name' => ['nullable', 'string', 'max:255'],
            'linked_account_type' => ['nullable', 'string', 'max:100'],
            'expense_cost_type' => ['nullable', 'string', 'max:100'],
            'expense_cost_account_id' => ['nullable', 'integer'],
            'expense_cost_account_name' => ['nullable', 'string', 'max:255'],
            'expense_cost_category_name' => ['nullable', 'string', 'max:255'],
            'requested_by_id' => ['nullable', 'integer'],
            'requested_by_name' => ['nullable', 'string', 'max:255'],
            'requested_by_email' => ['nullable', 'string', 'max:255'],
            'requested_by_type' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'in:submitted,pending'],
        ];
    }
}
