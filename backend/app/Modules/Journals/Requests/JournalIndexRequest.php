<?php

namespace App\Modules\Journals\Requests;

use App\Http\Requests\ApiIndexRequest;

class JournalIndexRequest extends ApiIndexRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'status' => ['nullable', 'string', 'max:50'],
            'transaction_type' => ['nullable', 'string', 'max:50'],
        ]);
    }

    public function filters(): array
    {
        return array_merge(parent::filters(), [
            'status' => $this->get('status'),
            'transaction_type' => $this->get('transaction_type'),
        ]);
    }
}
