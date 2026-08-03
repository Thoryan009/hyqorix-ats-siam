<?php

namespace App\Modules\Finance\Requests;

use App\Modules\Finance\Models\IncomeCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IncomeHeadRequest extends FormRequest
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

        if ($this->has('category_id') && !$this->has('income_category_id')) {
            $merged['income_category_id'] = $this->input('category_id');
        }

        if ($this->has('is_bills_payable_link')) {
            $merged['is_bills_payable_link'] = $this->boolean('is_bills_payable_link');
        }

        if (!empty($merged)) {
            $this->merge($merged);
        }
    }

    public function rules(): array
    {
        $headId = $this->route('incomeHead')?->id ?? $this->input('id');
        $categoryId = $this->input('income_category_id');

        return [
            'income_category_id' => ['required', 'integer', 'exists:income_categories,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('income_heads', 'name')
                    ->where(fn ($query) => $query->where('income_category_id', $categoryId))
                    ->ignore($headId),
            ],
            'base_price' => ['required', 'numeric', 'min:0'],
            'linked_accounts' => ['nullable', 'array'],
            'linked_accounts.*.account_category' => ['required_with:linked_accounts', 'string', 'max:255'],
            'is_bills_payable_link' => [
                'sometimes',
                'boolean',
                function (string $attribute, mixed $value, \Closure $fail) use ($categoryId) {
                    if (!$value) {
                        return;
                    }

                    $category = IncomeCategory::query()->find($categoryId);
                    if (!$category || $category->code !== 'other_income') {
                        $fail('Bills payable link can only be set for Operating Income heads.');
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
