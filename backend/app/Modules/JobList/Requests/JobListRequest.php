<?php

namespace App\Modules\JobList\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('job_list'); // for unique checks on update

        return [

            'name' => ['required', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'client_commission_per_candidate' => ['nullable', 'numeric', 'min:0'],
            'vacancy' => ['required', 'integer', 'min:1'],
            'experience' => ['required', 'string'],
            'min_age' => ['required', 'integer', 'min:0'],
            'max_age' => ['required', 'integer', 'gte:min_age'],
            'contract_length' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'qualification' => ['nullable', 'string'],
            'language' => ['nullable', 'string'],
            'salary' => ['nullable', 'string'],
            'deadline' => ['nullable', 'date'],
            'interview_date' => ['nullable', 'date'],
            'status' => ['required', 'string', Rule::in(['open', 'closed', 'hold'])],
            'work_order_id' => ['required', 'exists:work_orders,id'],
            'principal_id' => ['nullable', 'exists:principals,id'],
        ];
    }
}
