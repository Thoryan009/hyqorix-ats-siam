<?php

namespace App\Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinanceAccountCollectPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merged = [];

        if ($this->has('paymentMethod') && !$this->has('payment_method')) {
            $merged['payment_method'] = $this->input('paymentMethod');
        }

        if ($this->has('mainAccountId') && !$this->has('main_account_id')) {
            $merged['main_account_id'] = $this->input('mainAccountId');
        }

        if ($this->has('liabilityAccountId') && !$this->has('liability_account_id')) {
            $merged['liability_account_id'] = $this->input('liabilityAccountId');
        }

        if ($this->has('partyAccountId') && !$this->has('party_account_id')) {
            $merged['party_account_id'] = $this->input('partyAccountId');
        }

        if ($this->has('payerType') && !$this->has('payer_type')) {
            $merged['payer_type'] = $this->input('payerType');
        }

        if ($this->has('referenceNo') && !$this->has('reference_no')) {
            $merged['reference_no'] = $this->input('referenceNo');
        }

        if ($this->has('entryDate') && !$this->has('transaction_date')) {
            $merged['transaction_date'] = $this->input('entryDate');
        }

        if ($this->has('date') && !$this->has('transaction_date')) {
            $merged['transaction_date'] = $this->input('date');
        }

        if ($this->has('clientName') && !$this->has('client_name')) {
            $merged['client_name'] = $this->input('clientName');
        }

        if ($this->has('demandLetter') && !$this->has('demand_letter')) {
            $merged['demand_letter'] = $this->input('demandLetter');
        }

        if ($this->has('jobTitle') && !$this->has('job')) {
            $merged['job'] = $this->input('jobTitle');
        }

        if ($this->has('jobId') && !$this->has('job_list_id')) {
            $merged['job_list_id'] = $this->input('jobId');
        }

        if ($this->has('jobCode') && !$this->has('job_code')) {
            $merged['job_code'] = $this->input('jobCode');
        }

        if ($this->has('entryNo') && !$this->has('entry_no')) {
            $merged['entry_no'] = $this->input('entryNo');
        }

        if (!empty($merged)) {
            $this->merge($merged);
        }
    }

    public function rules(): array
    {
        $paymentMethod = (string) $this->input('payment_method');

        return [
            'payment_method' => ['required', Rule::in(['cash', 'bank', 'due', 'balance', 'expense_link', 'adjustment'])],
            'payer_type' => ['required', Rule::in(['agent', 'candidate', 'client'])],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transaction_date' => ['required', 'date'],
            'particular' => ['nullable', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'demand_letter' => ['nullable', 'string', 'max:255'],
            'job' => ['nullable', 'string', 'max:255'],
            'main_account_id' => [
                Rule::requiredIf(in_array($paymentMethod, ['cash', 'bank'], true)),
                'nullable',
                'integer',
                'exists:finance_accounts,id',
            ],
            'liability_account_id' => [
                Rule::requiredIf($paymentMethod === 'adjustment'),
                'nullable',
                'integer',
                'exists:finance_accounts,id',
            ],
            'party_account_id' => [
                Rule::requiredIf(function () {
                    $payerType = (string) $this->input('payer_type');

                    return in_array($payerType, ['agent', 'client'], true);
                }),
                'nullable',
                'integer',
                'exists:finance_accounts,id',
            ],
            'job_list_id' => ['nullable', 'integer', 'exists:job_lists,id'],
            'job_code' => ['nullable', 'string', 'max:100'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'entry_no' => ['nullable', 'string', 'max:100'],
            'candidates' => ['nullable', 'array'],
            'candidates.*.application_id' => ['nullable', 'integer', 'exists:applications,id'],
            'candidates.*.candidate_id' => ['nullable', 'integer', 'exists:applications,id'],
            'candidates.*.candidate_name' => ['nullable', 'string', 'max:255'],
            'candidates.*.passport_no' => ['nullable', 'string', 'max:100'],
            'candidates.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'candidates.*.amount' => ['nullable', 'numeric', 'min:0.01'],
            'candidates.*.collected_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
