<?php

namespace App\Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinanceAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merged = [];

        if ($this->has('status')) {
            $merged['status'] = $this->normalizeStatus($this->input('status'));
        }

        if ($this->has('link_to_purchase')) {
            $merged['link_to_purchase'] = filter_var(
                $this->input('link_to_purchase'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            ) ?? false;
        }

        if ($this->has('account_type')) {
            $merged['account_type'] = strtolower((string) $this->input('account_type'));
        }

        if ($this->has('current_balance') && !$this->has('balance')) {
            $merged['balance'] = $this->input('current_balance');
        }

        if ($this->has('head_id') && !$this->has('expense_head_id')) {
            $merged['expense_head_id'] = $this->input('head_id');
        }

        if ($this->has('head_id') && !$this->has('income_head_id') && $this->isIncomeCategory($this->input('category'))) {
            $merged['income_head_id'] = $this->input('head_id');
            unset($merged['expense_head_id']);
        }

        if ($this->has('category_id') && !$this->has('expense_category_id')) {
            $merged['expense_category_id'] = $this->input('category_id');
        }

        if ($this->has('category_id') && !$this->has('income_category_id') && $this->isIncomeCategory($this->input('category'))) {
            $merged['income_category_id'] = $this->input('category_id');
            unset($merged['expense_category_id']);
        }

        if (!empty($merged)) {
            $this->merge($merged);
        }
    }

    public function rules(): array
    {
        $accountId = $this->route('financeAccount')?->id ?? $this->input('id');
        $category = $this->input('category');
        $categories = config('finance_accounts.categories', []);

        $rules = [
            'category' => ['required', Rule::in($categories)],
            'account_name' => ['required', 'string', 'max:255'],
            'account_label' => ['nullable', 'string', 'max:255'],
            'account_type' => ['nullable', Rule::in(['cash', 'bank'])],
            'bank_id' => ['nullable', 'integer', 'exists:finance_banks,id'],
            'icon' => ['nullable', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'balance' => ['nullable', 'numeric'],
            'opening_balance' => ['nullable', 'numeric'],
            'opening_amount' => ['nullable', 'numeric', 'min:0'],
            'opening_amount_type' => ['nullable', 'string', Rule::in(['receivable', 'payable'])],
            'main_account_id' => ['nullable', 'integer', 'exists:finance_accounts,id'],
            'expense_head_id' => ['nullable', 'integer', 'exists:expense_heads,id'],
            'expense_category_id' => ['nullable', 'integer', 'exists:expense_categories,id'],
            'income_head_id' => ['nullable', 'integer', 'exists:income_heads,id'],
            'income_category_id' => ['nullable', 'integer', 'exists:income_categories,id'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'entity_id' => ['nullable', 'integer'],
            'bill_agent_id' => ['nullable', 'integer'],
            'metadata' => ['nullable', 'array'],
            'link_to_purchase' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];

        if ($category && in_array($category, ['direct_expense', 'client_recruitment', 'operating_expense'], true)) {
            $rules['expense_head_id'][] = Rule::unique('finance_accounts', 'expense_head_id')
                ->where(fn ($query) => $query->where('category', $category))
                ->ignore($accountId);
        }

        if ($category && in_array($category, ['recruitment_income', 'client_income', 'other_income'], true)) {
            $rules['income_head_id'][] = Rule::unique('finance_accounts', 'income_head_id')
                ->where(fn ($query) => $query->where('category', $category))
                ->ignore($accountId);
        }

        if ($category && in_array($category, ['agent', 'vendor', 'principal', 'client', 'staff', 'applicant'], true)) {
            $rules['entity_id'][] = Rule::unique('finance_accounts', 'entity_id')
                ->where(fn ($query) => $query->where('category', $category))
                ->ignore($accountId);
        }

        if ($category === 'main') {
            $rules['balance'][] = 'prohibited';
            $rules['opening_balance'][] = 'prohibited';
            $rules['opening_amount'][] = 'prohibited';
            $rules['opening_amount_type'][] = 'prohibited';
            $rules['main_account_id'][] = 'prohibited';
        }

        if ($category && in_array($category, ['agent', 'vendor', 'principal', 'client', 'staff', 'banks', 'owners'], true)) {
            $rules['opening_amount'][] = 'prohibited';
            $rules['opening_amount_type'][] = 'prohibited';
            $rules['main_account_id'][] = 'prohibited';
        }

        if ($category === 'owners') {
            $rules['account_name'][] = Rule::unique('finance_accounts', 'account_name')
                ->where(fn ($query) => $query->where('category', 'owners'))
                ->ignore($accountId);
        }

        if ($category === 'banks') {
            $rules['bank_id'] = [
                'required',
                'integer',
                'exists:finance_banks,id',
                Rule::unique('finance_accounts', 'bank_id')
                    ->where(fn ($query) => $query->where('category', 'banks'))
                    ->ignore($accountId),
            ];
        }

        if ($category !== 'asset') {
            $rules['link_to_purchase'][] = 'prohibited';
        }

        return $rules;
    }

    private function normalizeStatus(?string $status): string
    {
        return strtolower((string) $status) === 'inactive' ? 'inactive' : 'active';
    }

    private function isIncomeCategory(?string $category): bool
    {
        return in_array($category, ['recruitment_income', 'client_income', 'other_income'], true);
    }
}
