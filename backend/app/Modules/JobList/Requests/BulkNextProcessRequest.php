<?php

namespace App\Modules\JobList\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkNextProcessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // check application ids exists in applications table
            'application_ids' => [
                'required',
                'array',
                'min:1',
                Rule::exists('applications', 'id')->where(function ($query) {
                    $query->whereIn('id', $this->application_ids);
                }),
            ],

            'next_process_id' => [
                'integer',
               'exists:processes,id',
            ],


        ];
    }
}
