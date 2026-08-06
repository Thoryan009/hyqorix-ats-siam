<?php

namespace App\Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinanceBillEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $entryType = strtolower(trim((string) $this->input('entry_type', 'expense_bill')));
        if (!in_array($entryType, ['expense_bill', 'asset_purchase'], true)) {
            $entryType = 'expense_bill';
        }

        $this->merge(['entry_type' => $entryType]);
    }

    public function rules(): array
    {
        $entryType = strtolower(trim((string) $this->input('entry_type', 'expense_bill')));

        $rules = [
            'entry_type' => ['required', 'string', Rule::in(['expense_bill', 'asset_purchase'])],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'particular' => ['nullable', 'string', 'max:500'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'receipt_path' => ['nullable', 'array', 'max:10'],
            'receipt_path.*' => ['file', 'mimetypes:image/*', 'max:2048'],
            'linked_account_category' => ['nullable', 'string', 'max:100'],
            'linked_account_id' => ['nullable', 'integer'],
            'linked_account_name' => ['nullable', 'string', 'max:255'],
            'linked_account_type' => ['nullable', 'string', 'max:100'],
            'expense_cost_type' => ['nullable', 'string', 'max:100'],
            'expense_cost_account_id' => ['nullable', 'integer'],
            'expense_cost_account_name' => ['nullable', 'string', 'max:255'],
            'expense_cost_category_name' => ['nullable', 'string', 'max:255'],
            'application_id' => ['nullable', 'integer', 'exists:applications,id'],
            'demand_letter_id' => ['nullable', 'integer', 'exists:work_orders,id'],
            'requested_by_id' => ['nullable', 'integer'],
            'requested_by_name' => ['nullable', 'string', 'max:255'],
            'requested_by_email' => ['nullable', 'string', 'max:255'],
            'requested_by_type' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'in:submitted,pending'],
        ];

        if ($entryType === 'asset_purchase') {
            $rules['asset_account_id'] = [
                'required',
                'integer',
                Rule::exists('finance_accounts', 'id')->where(function ($query) {
                    $query->where('category', 'asset')
                        ->where('link_to_purchase', true)
                        ->where('status', 'active');
                }),
            ];
            $rules['category_id'] = ['prohibited'];
            $rules['head_id'] = ['prohibited'];
        } else {
            $rules['category_id'] = ['required', 'integer', 'exists:expense_categories,id'];
            $rules['head_id'] = ['required', 'integer', 'exists:expense_heads,id'];
            $rules['asset_account_id'] = ['prohibited'];
        }

        return $rules;
    }
}
