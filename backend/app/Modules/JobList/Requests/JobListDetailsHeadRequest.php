<?php

namespace App\Modules\JobList\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobListDetailsHeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        $id = $this->route('job_list_details_head'); // for unique check on update

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'amount' => ['required', 'numeric', 'min:0'],
            'amount_usd' => ['required', 'numeric', 'min:0'],
            'job_list_details_category_id' => [
                'required',
                'integer',
                'exists:job_list_details_categories,id',
            ],
        ];
    }
}
