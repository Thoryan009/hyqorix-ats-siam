<?php

namespace App\Modules\Finance\Requests;

use App\Modules\Finance\Models\ExpenseCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseHeadRequest extends FormRequest
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

        if ($this->has('category_id') && !$this->has('expense_category_id')) {
            $merged['expense_category_id'] = $this->input('category_id');
        }

        if ($this->has('is_bills_receivable_link')) {
            $merged['is_bills_receivable_link'] = $this->boolean('is_bills_receivable_link');
        }

        if ($this->has('is_depreciation_expense')) {
            $merged['is_depreciation_expense'] = $this->boolean('is_depreciation_expense');
        }

        if (!empty($merged)) {
            $this->merge($merged);
        }
    }

    public function rules(): array
    {
        $headId = $this->route('expenseHead')?->id ?? $this->input('id');
        $categoryId = $this->input('expense_category_id');

        return [
            'expense_category_id' => ['required', 'integer', 'exists:expense_categories,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('expense_heads', 'name')
                    ->where(fn ($query) => $query->where('expense_category_id', $categoryId))
                    ->ignore($headId),
            ],
            'base_price' => ['required', 'numeric', 'min:0'],
            'linked_accounts' => ['nullable', 'array'],
            'linked_accounts.*.account_category' => ['required_with:linked_accounts', 'string', 'max:255'],
            'is_bills_receivable_link' => [
                'sometimes',
                'boolean',
                function (string $attribute, mixed $value, \Closure $fail) use ($categoryId) {
                    if (!$value) {
                        return;
                    }

                    $category = ExpenseCategory::query()->find($categoryId);
                    if (!$category || $category->code !== 'operating_cost') {
                        $fail('Bills receivable link can only be set for Operating Expense heads.');
                    }
                },
            ],
            'is_depreciation_expense' => [
                'sometimes',
                'boolean',
                function (string $attribute, mixed $value, \Closure $fail) use ($categoryId) {
                    if (!$value) {
                        return;
                    }

                    $category = ExpenseCategory::query()->find($categoryId);
                    if (!$category || $category->code !== 'operating_cost') {
                        $fail('Depreciation Expense can only be marked on an Operating Expense head.');
                    }
                },
            ],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    private function normalizeStatus(?string $status): string
    {
        return strtolower((string) $status) === 'inactive' ? 'inactive' : 'active';
    }
}
