<?php

namespace App\Modules\Journals\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JournalTransactionTypeRequest extends FormRequest
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

        if ($this->has('subledger_required')) {
            $this->merge([
                'subledger_required' => in_array($this->input('subledger_required'), [1, '1', true, 'true'], true),
            ]);
        }

        if ($this->has('demand_letter_required')) {
            $this->merge([
                'demand_letter_required' => in_array($this->input('demand_letter_required'), [1, '1', true, 'true'], true),
            ]);
        }
    }

    public function rules(): array
    {
        $typeId = $this->route('journalTransactionType')?->id ?? $this->input('id');

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('journal_transaction_types', 'code')->ignore($typeId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'subledger_required' => ['nullable', 'boolean'],
            'demand_letter_required' => ['nullable', 'boolean'],
        ];
    }

    private function normalizeStatus(?string $status): string
    {
        return strtolower((string) $status) === 'inactive' ? 'inactive' : 'active';
    }
}
