<?php

namespace App\Modules\Journals\Requests;

use App\Modules\Journals\Models\Journal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class JournalPayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $partyType = trim((string) $this->input('party_type', ''));
        $partyId = $this->input('party_id');
        $projectId = trim((string) $this->input('project_id', ''));
        $referenceNo = trim((string) $this->input('reference_no', ''));
        $narration = trim((string) $this->input('narration', ''));

        $lines = collect($this->input('lines', []))->map(function ($line) {
            $line = is_array($line) ? $line : [];

            $line['account_id'] = $line['account_id'] === '' || $line['account_id'] === null
                ? null
                : (int) $line['account_id'];
            $line['sub_ledger'] = trim((string) ($line['sub_ledger'] ?? ''));
            $line['cost_type'] = trim((string) ($line['cost_type'] ?? '')) ?: null;
            $line['debit'] = ($line['debit'] ?? '') === '' || $line['debit'] === null
                ? 0
                : $line['debit'];
            $line['credit'] = ($line['credit'] ?? '') === '' || $line['credit'] === null
                ? 0
                : $line['credit'];

            return $line;
        })->values()->all();

        $this->merge([
            'party_type' => $partyType !== '' ? $partyType : null,
            'party_id' => $partyId === '' || $partyId === null ? null : (int) $partyId,
            'project_id' => $projectId !== '' ? $projectId : null,
            'reference_no' => $referenceNo !== '' ? $referenceNo : null,
            'narration' => $narration !== '' ? $narration : null,
            'lines' => $lines,
        ]);
    }

    public function rules(): array
    {
        return [
            'voucher_date' => ['required', 'date'],
            'transaction_type' => [
                'required',
                'string',
                'max:50',
                Rule::exists('journal_transaction_types', 'code')->where(
                    fn ($query) => $query->where('status', 'active')
                ),
            ],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'party_type' => ['nullable', 'string', 'max:50'],
            'party_id' => ['nullable', 'integer', 'exists:parties,id'],
            'project_id' => ['nullable', 'string', 'max:100'],
            'narration' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'integer', 'exists:chart_of_accounts,id'],
            'lines.*.sub_ledger' => ['nullable', 'string', 'max:255'],
            'lines.*.cost_type' => ['nullable', 'string', Rule::in(Journal::COST_TYPES)],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $lines = $this->input('lines', []);
            $debitTotal = 0.0;
            $creditTotal = 0.0;

            foreach ($lines as $index => $line) {
                $debit = round((float) ($line['debit'] ?? 0), 2);
                $credit = round((float) ($line['credit'] ?? 0), 2);
                $row = $index + 1;

                if ($debit > 0 && $credit > 0) {
                    $validator->errors()->add(
                        "lines.{$index}.debit",
                        "Row {$row}: a line cannot have both debit and credit."
                    );
                }

                if ($debit <= 0 && $credit <= 0) {
                    $validator->errors()->add(
                        "lines.{$index}.debit",
                        "Row {$row}: enter a debit or credit amount."
                    );
                }

                $debitTotal += $debit;
                $creditTotal += $credit;
            }

            if (abs(round($debitTotal, 2) - round($creditTotal, 2)) > 0.009) {
                $validator->errors()->add(
                    'lines',
                    'Debit and credit totals must be equal before posting.'
                );
            }
        });
    }
}
