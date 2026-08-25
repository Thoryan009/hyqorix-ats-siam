<?php

namespace App\Modules\Parties\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PartyBulkStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $parties = collect($this->input('parties', []))->map(function ($party) {
            $party = is_array($party) ? $party : [];

            if (array_key_exists('status', $party)) {
                $party['status'] = strtolower((string) $party['status']) === 'inactive'
                    ? 'inactive'
                    : 'active';
            }

            if (array_key_exists('code', $party)) {
                $party['code'] = trim((string) $party['code']);
            }

            if (array_key_exists('name', $party)) {
                $party['name'] = trim((string) $party['name']);
            }

            if (array_key_exists('remarks', $party)) {
                $party['remarks'] = trim((string) ($party['remarks'] ?? ''));
            }

            $party['opening_debit'] = ($party['opening_debit'] ?? '') === '' || $party['opening_debit'] === null
                ? 0
                : $party['opening_debit'];

            $party['opening_credit'] = ($party['opening_credit'] ?? '') === '' || $party['opening_credit'] === null
                ? 0
                : $party['opening_credit'];

            return $party;
        })->values()->all();

        $this->merge(['parties' => $parties]);
    }

    public function rules(): array
    {
        return [
            'parties' => ['required', 'array', 'min:1'],
            'parties.*.code' => [
                'required',
                'string',
                'max:20',
                'distinct',
                Rule::unique('parties', 'code'),
            ],
            'parties.*.name' => ['required', 'string', 'max:255'],
            'parties.*.type' => [
                'required',
                'string',
                'max:50',
                Rule::exists('party_types', 'code')->where(fn ($query) => $query->where('status', 'active')),
            ],
            'parties.*.opening_debit' => ['nullable', 'numeric', 'min:0'],
            'parties.*.opening_credit' => ['nullable', 'numeric', 'min:0'],
            'parties.*.remarks' => ['nullable', 'string'],
            'parties.*.status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function messages(): array
    {
        return [
            'parties.*.code.distinct' => 'Duplicate party IDs are not allowed in the same request.',
            'parties.*.code.unique' => 'One or more party IDs already exist.',
        ];
    }
}
