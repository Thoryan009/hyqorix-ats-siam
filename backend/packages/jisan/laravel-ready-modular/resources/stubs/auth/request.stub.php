<?php

namespace App\Modules\__MODEL__\Requests;

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
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }
}
