<?php

namespace App\Modules\Accounts\Requests;

use App\Http\Requests\ApiIndexRequest;

class ChartOfAccountIndexRequest extends ApiIndexRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'status' => ['nullable', 'string'],
            'type' => ['nullable', 'string', 'max:100'],
            'financial_statement' => ['nullable', 'string', 'max:100'],
            'normal_balance' => ['nullable', 'string'],
        ]);
    }

    public function filters(): array
    {
        return array_merge(parent::filters(), [
            'status' => $this->get('status'),
            'type' => $this->get('type'),
            'financial_statement' => $this->get('financial_statement'),
            'normal_balance' => $this->get('normal_balance'),
        ]);
    }
}
