<?php

namespace App\Modules\PassportHandover\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PassportHandoverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isPermanent = $this->boolean('is_permanent');
        $isGroup = $this->input('type') === 'group';
        $isSingle = $this->input('type') === 'single';

        return [
            'type' => ['required', Rule::in(['single', 'group'])],
            'is_permanent' => ['sometimes', 'boolean'],
            'taker_name' => ['required', 'string', 'max:255'],
            'taker_phone' => ['required', 'string', 'max:50'],
            'taken_at' => ['required', 'date'],

            'expected_return_date' => [
                'nullable',
                'date',
                Rule::requiredIf($isGroup && !$isPermanent),
                Rule::prohibitedIf($isPermanent),
            ],
            'taken_reason' => ['nullable', 'string', 'required_if:type,group'],
            'return_date' => ['nullable', 'date', Rule::prohibitedIf($isPermanent)],

            'entries' => ['required_if:type,single', 'array', 'min:1'],
            'entries.*.application_id' => ['required', 'integer', 'exists:applications,id'],
            'entries.*.passport_no' => ['required', 'string', 'max:255'],
            'entries.*.expected_return_date' => [
                'nullable',
                'date',
                Rule::requiredIf($isSingle && !$isPermanent),
                Rule::prohibitedIf($isPermanent),
            ],
            'entries.*.taken_reason' => ['required', 'string'],
            'entries.*.return_date' => ['nullable', 'date', Rule::prohibitedIf($isPermanent)],

            'applications' => ['required_if:type,group', 'array', 'min:1'],
            'applications.*.application_id' => ['required', 'integer', 'exists:applications,id'],
            'applications.*.passport_no' => ['required', 'string', 'max:255'],
        ];
    }
}
