<?php

namespace App\Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinanceBankRequest extends FormRequest
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
    }

    public function rules(): array
    {
        $bankId = $this->route('financeBank')?->id ?? $this->input('id');

        return [
            'bank_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('finance_banks', 'bank_name')->ignore($bankId),
            ],
            'swift_code' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'branch_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    private function normalizeStatus(?string $status): string
    {
        return strtolower((string) $status) === 'inactive' ? 'inactive' : 'active';
    }
}
