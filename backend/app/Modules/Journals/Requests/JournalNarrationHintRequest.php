<?php

namespace App\Modules\Journals\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JournalNarrationHintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'voucher_date' => ['nullable', 'date'],
            'transaction_type' => ['nullable', 'string', 'max:100'],
            'transaction_type_label' => ['nullable', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'party_label' => ['nullable', 'string', 'max:255'],
            'project_label' => ['nullable', 'string', 'max:255'],
            'lines' => ['nullable', 'array', 'max:50'],
            'lines.*.account_label' => ['nullable', 'string', 'max:255'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.cost_type_label' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function context(): array
    {
        $lines = collect($this->input('lines', []))
            ->map(function ($line) {
                $line = is_array($line) ? $line : [];

                return [
                    'account_label' => trim((string) ($line['account_label'] ?? '')),
                    'debit' => (float) ($line['debit'] ?? 0),
                    'credit' => (float) ($line['credit'] ?? 0),
                    'cost_type_label' => trim((string) ($line['cost_type_label'] ?? '')),
                ];
            })
            ->filter(function ($line) {
                return $line['account_label'] !== ''
                    || $line['debit'] > 0
                    || $line['credit'] > 0;
            })
            ->values()
            ->all();

        return [
            'voucher_date' => $this->input('voucher_date'),
            'transaction_type' => trim((string) $this->input('transaction_type', '')),
            'transaction_type_label' => trim((string) $this->input('transaction_type_label', '')),
            'reference_no' => trim((string) $this->input('reference_no', '')),
            'party_label' => trim((string) $this->input('party_label', '')),
            'project_label' => trim((string) $this->input('project_label', '')),
            'lines' => $lines,
        ];
    }
}
