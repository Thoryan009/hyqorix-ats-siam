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
        ];
    }

    private function normalizeStatus(?string $status): string
    {
        return strtolower((string) $status) === 'inactive' ? 'inactive' : 'active';
    }
}
