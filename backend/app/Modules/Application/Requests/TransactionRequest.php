<?php

namespace App\Modules\Application\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return $this->updateRules();
        }

        return $this->storeRules();
    }

    private function storeRules(): array
    {
        return [
            'discount_amount' => ['nullable', 'numeric', 'min:0', 'lte:total_amount'],
            'paid_amount' => ['required', 'numeric', 'min:0', 'lte:total_amount'],
            'payment_method' => ['required', Rule::in(['bank', 'bkash', 'cash'])],
            'payment_date' => ['required', 'date'],
            'payment_time' => ['required', 'date_format:H:i'],
            'status' => ['nullable', 'string', Rule::in(['draft'])],
            'remarks' => ['nullable', 'string'],
            'application_id' => ['required', 'exists:applications,id'],
        ];
    }

    private function updateRules(): array
    {
        $id = $this->route('transaction');

        return [
            'transaction_id'   => ['sometimes', 'string', Rule::unique('transactions', 'transaction_id')->ignore($id)],
            'bill_no'          => ['sometimes', 'nullable', 'string'],
            'total_amount'     => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'total_amount_usd' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'paid_amount'      => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'paid_amount_usd'  => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'discount_amount'  => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'payment_method'   => ['sometimes', Rule::in(['bank', 'bkash', 'cash', 'pending'])],
            'status'           => ['sometimes', 'string', Rule::in(['draft', 'due', 'paid', 'bill-generated', 'invoice-generated', 'invoice-sent', 'cancelled'])],
            'payment_date'     => ['sometimes', 'date'],
            'payment_time'     => ['sometimes', 'date_format:H:i'],
            'remarks'          => ['sometimes', 'nullable', 'string'],
            'application_id'   => ['sometimes', 'exists:applications,id'],
        ];
    }
}
