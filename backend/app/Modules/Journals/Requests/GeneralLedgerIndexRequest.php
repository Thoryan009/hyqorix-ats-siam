<?php

namespace App\Modules\Journals\Requests;

use App\Http\Requests\ApiIndexRequest;

class GeneralLedgerIndexRequest extends ApiIndexRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'account_id' => ['nullable', 'integer', 'min:1'],
            'party_ref' => ['nullable', 'string', 'max:255'],
        ]);
    }

    public function filters(): array
    {
        return array_merge(parent::filters(), [
            'account_id' => $this->filled('account_id') ? (int) $this->get('account_id') : null,
            'party_ref' => trim((string) $this->get('party_ref', '')) ?: null,
        ]);
    }
}
