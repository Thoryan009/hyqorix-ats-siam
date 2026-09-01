<?php

namespace App\Modules\Employee\Requests;

use App\Modules\Auth\Models\User;
use App\Modules\Employee\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    private array $fileRule = [
        'nullable',
        'file',
        'mimetypes:image/*',
        'max:400', // 1MB
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee');
        $employee = $employeeId ? Employee::find($employeeId) : null;
        $userId   = $employee?->user_id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'username' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('employees', 'username')->ignore($employeeId),
            ],

            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->ignore($userId),
            ],


            'whatsapp_no' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'whatsapp_no')->ignore($userId),
            ],


            'password' => [
                $employee ? 'nullable' : 'required',
                'string',
                'min:8',
            ],
            'role_ids' => [
                'required',
                'array',
                'min:1',
            ],


            'role_ids.*' => [
                'integer',
                'exists:roles,id',
            ],

             'department_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'department_ids.*' => [
                'integer',
                'exists:departments,id',
            ],

            'designation_id' => [
                'required',
                'integer',
                'exists:designations,id',
            ],
            'send_credentials' => [
                'required',
                'integer',
                'in:0,1',
            ],
            'show_ats_summary' => [
                'required',
                'integer',
                'in:0,1',
            ],
            'manager_approval' => [
                'required',
                'integer',
                'in:0,1',
            ],
            'status' => [
                'required',
                Rule::in(User::STATUSES),
            ],

            'image_path' => $this->fileRule,
            'create_party_account' => ['nullable', Rule::in([0, 1, '0', '1', true, false])],
        ];
    }
}
