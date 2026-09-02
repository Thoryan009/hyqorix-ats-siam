<?php

namespace App\Modules\Accounts\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GrossProfitIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'job_list_id' => ['nullable', 'integer', 'exists:job_lists,id'],
            'work_order_id' => ['nullable', 'integer', 'exists:work_orders,id'],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'agent_id' => ['nullable', 'integer', 'exists:agents,id'],
            'principal_id' => ['nullable', 'integer', 'exists:principals,id'],
        ];
    }

    public function filters(): array
    {
        return [
            'from_date' => $this->input('from_date'),
            'to_date' => $this->input('to_date'),
            'job_list_id' => $this->filled('job_list_id') ? (int) $this->input('job_list_id') : null,
            'work_order_id' => $this->filled('work_order_id') ? (int) $this->input('work_order_id') : null,
            'client_id' => $this->filled('client_id') ? (int) $this->input('client_id') : null,
            'agent_id' => $this->filled('agent_id') ? (int) $this->input('agent_id') : null,
            'principal_id' => $this->filled('principal_id') ? (int) $this->input('principal_id') : null,
        ];
    }
}
