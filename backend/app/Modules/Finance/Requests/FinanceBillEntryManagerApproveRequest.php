<?php

namespace App\Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinanceBillEntryManagerApproveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'approval_remarks' => ['nullable', 'string'],
            'approved_by' => ['nullable', 'string', 'max:255'],
        ];
    }
}
