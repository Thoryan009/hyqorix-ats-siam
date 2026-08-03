<?php

namespace App\Modules\Application\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplicationProcessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('application_process'); // for update scenarios if needed later

        return [
            'application_id' => [
                'required',
                'integer',
                'exists:applications,id',
            ],

            'process_id' => [
                'required',
                'integer',
                'exists:processes,id',
            ],

            'status' => [
                'required',
                'string',
                'max:30',
                Rule::in([
                    'pending',
                    'draft',
                    'completed',
                    'rejected',
                ]),
            ],

            'data' => [
                'nullable',
                'array', // JSON column
            ],
            'created_by' => [
                'nullable',
                'string',
                'exists:users,username',
            ],
            'updated_by' => [
                'nullable',
                'string',
                'exists:users,username',
            ],

            'started_at' => [
                'nullable',
                'date',
            ],

            'completed_at' => [
                'nullable',
                'date',
                'after_or_equal:started_at',
            ],

            'remarks' => [
                'nullable',
                'string',
            ],
        ];
    }
}
