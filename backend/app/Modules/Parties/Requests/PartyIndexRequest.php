<?php

namespace App\Modules\Parties\Requests;

use App\Http\Requests\ApiIndexRequest;

class PartyIndexRequest extends ApiIndexRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'status' => ['nullable', 'string'],
            'type' => ['nullable', 'string', 'max:50'],
            'job_list_id' => ['nullable', 'integer', 'min:1'],
        ]);
    }

    public function filters(): array
    {
        return array_merge(parent::filters(), [
            'status' => $this->get('status'),
            'type' => $this->get('type'),
            'job_list_id' => $this->integer('job_list_id') ?: null,
        ]);
    }
}
