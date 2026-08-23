<?php

namespace App\Modules\Parties\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PartyRequest extends FormRequest
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

        if ($this->has('opening_debit')) {
            $this->merge([
                'opening_debit' => $this->input('opening_debit') === null || $this->input('opening_debit') === ''
                    ? 0
                    : $this->input('opening_debit'),
            ]);
        }

        if ($this->has('opening_credit')) {
            $this->merge([
                'opening_credit' => $this->input('opening_credit') === null || $this->input('opening_credit') === ''
                    ? 0
                    : $this->input('opening_credit'),
            ]);
        }
    }

    public function rules(): array
    {
        $partyId = $this->route('party')?->id ?? $this->input('id');

        return [
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('parties', 'code')->ignore($partyId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'opening_debit' => ['nullable', 'numeric', 'min:0'],
            'opening_credit' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    private function normalizeStatus(?string $status): string
    {
        return strtolower((string) $status) === 'inactive' ? 'inactive' : 'active';
    }
}
