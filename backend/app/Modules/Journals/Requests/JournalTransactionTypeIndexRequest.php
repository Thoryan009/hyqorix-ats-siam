<?php

namespace App\Modules\Journals\Requests;

use App\Http\Requests\ApiIndexRequest;

class JournalTransactionTypeIndexRequest extends ApiIndexRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'status' => ['nullable', 'string'],
        ]);
    }

    public function filters(): array
    {
        return array_merge(parent::filters(), [
            'status' => $this->get('status'),
        ]);
    }
}
