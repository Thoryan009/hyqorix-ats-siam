<?php

namespace App\Modules\JobList\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobListDetailsCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }
}
