<?php

namespace App\Modules\Accounts\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChartOfAccountRequest extends FormRequest
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

        if ($this->has('normal_balance')) {
            $this->merge([
                'normal_balance' => strtolower((string) $this->input('normal_balance')) === 'credit'
                    ? 'credit'
                    : 'debit',
            ]);
        }

        if ($this->has('code')) {
            $this->merge([
                'code' => trim((string) $this->input('code')),
            ]);
        }
    }

    public function rules(): array
    {
        $accountId = $this->route('chartOfAccount')?->id ?? $this->input('id');

        return [
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('chart_of_accounts', 'code')->ignore($accountId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'financial_statement' => ['required', 'string', 'max:100'],
            'normal_balance' => ['required', Rule::in(['debit', 'credit'])],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    private function normalizeStatus(?string $status): string
    {
        return strtolower((string) $status) === 'inactive' ? 'inactive' : 'active';
    }
}
