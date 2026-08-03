<?php

namespace App\Modules\JobList\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobListDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

   public function rules(): array
    {
        $id = $this->route('job_list_detail'); // for update scenarios if needed

        return [
            'job_list_id' => [
                'required',
                'integer',
                'exists:job_lists,id',
            ],
            'job_list_details_head_id' => [
                'required',
                'integer',
                'exists:job_list_details_heads,id',
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0',
            ],
            'amount_usd' => [
                'required',
                'numeric',
                'min:0',
            ],
        ];
    }
}
