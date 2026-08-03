<?php

namespace App\Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinanceBillEntryManagerApproveBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct', 'exists:finance_bill_entries,id'],
            'approval_remarks' => ['nullable', 'string'],
            'approved_by' => ['nullable', 'string', 'max:255'],
        ];
    }
}
