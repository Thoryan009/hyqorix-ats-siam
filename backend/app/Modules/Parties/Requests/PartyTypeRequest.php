<?php

namespace App\Modules\Parties\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PartyTypeRequest extends FormRequest
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

        if ($this->has('code')) {
            $this->merge([
                'code' => trim((string) $this->input('code')),
            ]);
        }

        if ($this->has('name')) {
            $this->merge([
                'name' => trim((string) $this->input('name')),
            ]);
        }

        if ($this->has('sort_order')) {
            $this->merge([
                'sort_order' => $this->input('sort_order') === null || $this->input('sort_order') === ''
                    ? 0
                    : $this->input('sort_order'),
            ]);
        }

        if ($this->has('apply_job_filter')) {
            $this->merge([
                'apply_job_filter' => in_array($this->input('apply_job_filter'), [1, '1', true, 'true'], true),
            ]);
        }
    }

    public function rules(): array
    {
        $partyTypeId = $this->route('partyType')?->id ?? $this->input('id');

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('party_types', 'code')->ignore($partyTypeId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'source_module' => [
                'nullable',
                'string',
                'max:50',
                Rule::in(array_keys(config('parties.source_modules', []))),
                Rule::unique('party_types', 'source_module')->ignore($partyTypeId),
            ],
            'apply_job_filter' => ['nullable', 'boolean'],
        ];
    }

    private function normalizeStatus(?string $status): string
    {
        return strtolower((string) $status) === 'inactive' ? 'inactive' : 'active';
    }
}
