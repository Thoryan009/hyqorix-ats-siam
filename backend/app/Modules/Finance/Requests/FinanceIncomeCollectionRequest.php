<?php

namespace App\Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinanceIncomeCollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merged = [];

        if ($this->has('category_id') && !$this->has('income_category_id')) {
            $merged['income_category_id'] = $this->input('category_id');
        }

        if ($this->has('head_id') && !$this->has('income_head_id')) {
            $merged['income_head_id'] = $this->input('head_id');
        }

        if ($this->has('main_account_id') && !$this->has('receive_account_id')) {
            $merged['receive_account_id'] = $this->input('main_account_id');
        }

        if ($this->has('liabilityAccountId') && !$this->has('liability_account_id')) {
            $merged['liability_account_id'] = $this->input('liabilityAccountId');
        }

        if (!empty($merged)) {
            $this->merge($merged);
        }
    }

    public function rules(): array
    {
        $paymentMethod = strtolower((string) $this->input('payment_method', 'cash'));

        return [
            'category_id' => ['nullable', 'integer'],
            'income_category_id' => ['required', 'integer', 'exists:income_categories,id'],
            'head_id' => ['nullable', 'integer'],
            'income_head_id' => ['required', 'integer', 'exists:income_heads,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'billed_amount' => ['nullable', 'numeric', 'min:0'],
            'collection_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', Rule::in(['cash', 'bank', 'due', 'expense_link', 'adjustment'])],
            'particular' => ['nullable', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string'],
            'receive_account_id' => [
                Rule::requiredIf(in_array($paymentMethod, ['cash', 'bank'], true)),
                'nullable',
                'integer',
                'exists:finance_accounts,id',
            ],
            'main_account_id' => ['nullable', 'integer', 'exists:finance_accounts,id'],
            'liability_account_id' => [
                Rule::requiredIf($paymentMethod === 'adjustment'),
                'nullable',
                'integer',
                'exists:finance_accounts,id',
            ],
            'linked_account_category' => ['nullable', 'string', 'max:100'],
            'linked_account_id' => ['nullable', 'integer', 'exists:finance_accounts,id'],
            'linked_account_name' => ['nullable', 'string', 'max:255'],
            'linked_account_type' => ['nullable', 'string', 'max:100'],
            'job_list_id' => ['nullable', 'integer'],
            'job_id' => ['nullable', 'integer'],
            'job_code' => ['nullable', 'string', 'max:100'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'candidates' => ['nullable', 'array'],
            'candidates.*.application_id' => ['nullable', 'integer'],
            'candidates.*.candidate_id' => ['nullable', 'integer'],
            'candidates.*.candidate_name' => ['nullable', 'string', 'max:255'],
            'candidates.*.passport_no' => ['nullable', 'string', 'max:100'],
            'candidates.*.amount' => ['nullable', 'numeric', 'min:0'],
            'candidates.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'collected_by_id' => ['nullable', 'integer'],
            'collected_by_name' => ['nullable', 'string', 'max:255'],
            'settles_income_collection_id' => ['nullable', 'integer', 'exists:finance_income_collections,id'],
        ];
    }
}
