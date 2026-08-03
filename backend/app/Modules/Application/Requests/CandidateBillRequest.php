<?php

namespace App\Modules\Application\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CandidateBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('candidate-bills'); // for unique checks on update

        return [
            'discount_amount' => ['nullable', 'numeric', 'min:0', 'lte:total_amount'],
            'paid_amount' => ['required', 'numeric', 'min:0', 'lte:total_amount'],
            'payment_method' => ['required', Rule::in(['bank', 'bkash'])],
            'payment_date' => ['required', 'date'],
            'payment_time' => ['required', 'date_format:H:i'],
            'status' => ['nullable', 'string', Rule::in(['draft'])],
            'remarks' => ['nullable', 'string'],
            'application_id' => ['required', 'exists:applications,id'],
        ];
    }
}
