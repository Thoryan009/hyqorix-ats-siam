<?php

namespace App\Modules\__PARENT_MODEL__\Requests;

use Illuminate\Foundation\Http\FormRequest;

class __MODEL__Request extends FormRequest
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
