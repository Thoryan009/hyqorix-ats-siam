<?php

namespace App\Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinanceBillEntryPayPayableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $paymentMethod = strtolower((string) $this->input('payment_method', 'cash'));
        $isIncomeLink = $paymentMethod === 'income_link';

        return [
            'pay_amount' => ['required', 'numeric', 'min:0.01'],
            'particular' => ['nullable', 'string', 'max:500'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'voucher_no' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'approval_remarks' => ['nullable', 'string'],
            'payment_method' => ['required', 'string', Rule::in(['cash', 'bank', 'income_link'])],
            'payment_account_category' => [
                Rule::requiredIf(!$isIncomeLink),
                'nullable',
                'string',
                'max:100',
            ],
            'payment_account_type' => ['nullable', 'string', 'max:100'],
            'payment_account_id' => [
                Rule::requiredIf(!$isIncomeLink),
                'nullable',
                'integer',
                'exists:finance_accounts,id',
            ],
            'payment_account_name' => ['nullable', 'string', 'max:255'],
            'approved_by' => ['nullable', 'string', 'max:255'],
        ];
    }
}
