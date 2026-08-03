<?php

namespace App\Modules\WorkOrder\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkOrderRequest extends FormRequest
{
    private array $fileRule = [
        'nullable',
        'file',
        'mimetypes:image/*,application/pdf',
        'max:400', // 1MB
    ];
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        $id = $this->route('work_order'); // for unique check on update

        return [
            'candidates' => ['nullable', 'integer', 'min:1'],
            'end_date' => ['nullable', 'date', 'after_or_equal:today'],
            'employee_id' => ['required', 'exists:employees,id'],
            'client_id' => ['required', 'exists:clients,id'],
            'work_order_path' => $this->fileRule,
            'visa_issue_number' => ['nullable', 'string', 'min:1'],
            'sponsor_id' => ['nullable', 'string'],
        ];
    }
}
