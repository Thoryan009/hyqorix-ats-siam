<?php

namespace App\Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinanceIncomeTaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('status')) {
            $this->merge([
                'status' => $this->normalizeStatus($this->input('status')),
            ]);
        }

        if ($this->has('tax_amount') && $this->input('tax_amount') === '') {
            $this->merge(['tax_amount' => 0]);
        }

        if ($this->has('tax_rate') && $this->input('tax_rate') === '') {
            $this->merge(['tax_rate' => null]);
        }

        if ($this->has('payment_date') && $this->input('payment_date') === '') {
            $this->merge(['payment_date' => null]);
        }

        if ($this->has('notes') && $this->input('notes') === '') {
            $this->merge(['notes' => null]);
        }
    }

    public function rules(): array
    {
        $incomeTaxId = $this->route('financeIncomeTax')?->id ?? $this->input('id');
        $currentYear = (int) date('Y');

        return [
            'year' => [
                'required',
                'integer',
                'min:2000',
                'max:' . ($currentYear + 1),
                Rule::unique('finance_income_taxes', 'year')->ignore($incomeTaxId),
            ],
            'tax_amount' => ['required', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'net_profit' => ['nullable', 'numeric'],
            'payment_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ];
    }

    public function messages(): array
    {
        return [
            'year.unique' => 'An income tax entry already exists for this year. Only one entry per year is allowed.',
        ];
    }

    private function normalizeStatus(?string $status): string
    {
        return strtolower((string) $status) === 'inactive' ? 'inactive' : 'active';
    }
}
