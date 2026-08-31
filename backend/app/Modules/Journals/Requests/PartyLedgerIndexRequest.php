<?php

namespace App\Modules\Journals\Requests;

use App\Http\Requests\ApiIndexRequest;

class PartyLedgerIndexRequest extends ApiIndexRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'party_type' => ['nullable', 'string', 'max:50'],
            'party_ref' => ['nullable', 'string', 'max:255'],
            'party_id' => ['nullable', 'integer', 'min:1'],
        ]);
    }

    public function filters(): array
    {
        return array_merge(parent::filters(), [
            'party_type' => trim((string) $this->get('party_type', '')) ?: null,
            'party_ref' => trim((string) $this->get('party_ref', '')) ?: null,
            'party_id' => $this->filled('party_id') ? (int) $this->get('party_id') : null,
        ]);
    }
}
