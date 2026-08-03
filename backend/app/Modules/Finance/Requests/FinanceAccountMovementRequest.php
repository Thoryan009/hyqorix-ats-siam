<?php

namespace App\Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinanceAccountMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merged = [];

        if ($this->has('fromAccountId') && !$this->has('from_account_id')) {
            $merged['from_account_id'] = $this->input('fromAccountId');
        }

        if ($this->has('toAccountId') && !$this->has('to_account_id')) {
            $merged['to_account_id'] = $this->input('toAccountId');
        }

        if ($this->has('referenceNo') && !$this->has('reference_no')) {
            $merged['reference_no'] = $this->input('referenceNo');
        }

        if ($this->has('date') && !$this->has('transaction_date')) {
            $merged['transaction_date'] = $this->input('date');
        }

        if (!empty($merged)) {
            $this->merge($merged);
        }
    }

    public function rules(): array
    {
        return [
            'from_account_id' => ['required', 'integer', 'exists:finance_accounts,id'],
            'to_account_id' => ['required', 'integer', 'different:from_account_id', 'exists:finance_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'particular' => ['nullable', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string'],
            'transaction_date' => ['required', 'date'],
        ];
    }
}
