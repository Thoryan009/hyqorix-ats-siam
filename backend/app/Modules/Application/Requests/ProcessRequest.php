<?php

namespace App\Modules\Application\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('process'); // for unique checks on update

        return [
            'name' => [
                'required',
                'string',
            ],

            'duration' => [
                'required',
                'integer',
                'min:0',
                'max:255', // suitable for tinyint
            ],
            'validity' => [
                'nullable',
                'integer',
                'min:0',
                'max:365', // suitable for tinyint
            ],
            'notify_before' => [
                'nullable',
                'integer',
                'min:0',
                'max:255', // suitable for tinyint
            ],
        ];
    }
}
