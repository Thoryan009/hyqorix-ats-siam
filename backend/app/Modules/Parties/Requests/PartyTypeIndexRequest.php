<?php

namespace App\Modules\Parties\Requests;

use App\Http\Requests\ApiIndexRequest;

class PartyTypeIndexRequest extends ApiIndexRequest
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
