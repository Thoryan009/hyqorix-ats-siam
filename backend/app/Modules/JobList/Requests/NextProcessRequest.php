<?php

namespace App\Modules\JobList\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NextProcessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // check application id exists in applications table
            'application_id' => [
                'integer',
                'exists:applications,id',
            ],

            'next_process_id' => [
                'integer',
               'exists:processes,id',
            ],
            // here process data is a js object
            'processData' => [
                'array',
            ],

        ];
    }
}
