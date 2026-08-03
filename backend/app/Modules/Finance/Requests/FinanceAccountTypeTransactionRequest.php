<?php

namespace App\Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinanceAccountTypeTransactionRequest extends FormRequest
{
    private const ACCOUNT_CATEGORIES = ['main', 'staff', 'agent', 'vendor', 'principal', 'client', 'applicant'];

    private const TRANSACTION_TYPES = [
        'loan',
        'advanced',
        'loan_repay',
        'advanced_repay',
        'adjust_minus',
        'adjust_plus',
    ];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merged = [];

        $map = [
            'transactionType' => 'transaction_type',
            'referenceNo' => 'reference_no',
            'accountCategory' => 'account_category',
            'mainAccountType' => 'main_account_type',
            'accountId' => 'account_id',
            'fromAccountCategory' => 'from_account_category',
            'fromMainAccountType' => 'from_main_account_type',
            'fromAccountId' => 'from_account_id',
            'toAccountCategory' => 'to_account_category',
            'toMainAccountType' => 'to_main_account_type',
            'toAccountId' => 'to_account_id',
        ];

        foreach ($map as $camel => $snake) {
            if ($this->has($camel) && !$this->has($snake)) {
                $merged[$snake] = $this->input($camel);
            }
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
        $type = (string) $this->input('transaction_type');
        $isAdjustment = in_array($type, ['adjust_minus', 'adjust_plus'], true);

        $rules = [
            'transaction_type' => ['required', 'string', Rule::in(self::TRANSACTION_TYPES)],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transaction_date' => ['required', 'date'],
            'date' => ['sometimes', 'date'],
            'particular' => ['nullable', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string'],
        ];

        if ($isAdjustment) {
            $rules['account_category'] = ['required', 'string', Rule::in(self::ACCOUNT_CATEGORIES)];
            $rules['account_id'] = ['required', 'integer', 'exists:finance_accounts,id'];
            $rules['main_account_type'] = ['nullable', 'string', Rule::in(['Cash', 'Bank', 'cash', 'bank'])];
        } else {
            $rules['from_account_category'] = ['required', 'string', Rule::in(self::ACCOUNT_CATEGORIES)];
            $rules['from_account_id'] = ['required', 'integer', 'exists:finance_accounts,id'];
            $rules['to_account_category'] = ['required', 'string', Rule::in(self::ACCOUNT_CATEGORIES)];
            $rules['to_account_id'] = [
                'required',
                'integer',
                'different:from_account_id',
                'exists:finance_accounts,id',
            ];
            $rules['from_main_account_type'] = ['nullable', 'string', Rule::in(['Cash', 'Bank', 'cash', 'bank'])];
            $rules['to_main_account_type'] = ['nullable', 'string', Rule::in(['Cash', 'Bank', 'cash', 'bank'])];
        }

        return $rules;
    }
}
