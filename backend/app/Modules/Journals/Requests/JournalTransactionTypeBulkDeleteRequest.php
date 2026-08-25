<?php

namespace App\Modules\Journals\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JournalTransactionTypeBulkDeleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:journal_transaction_types,id'],
        ];
    }
}
