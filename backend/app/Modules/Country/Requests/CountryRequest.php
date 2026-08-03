<?php

namespace App\Modules\Country\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class CountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // move policy logic here later
    }

   public function rules(): array
{
    $id = $this->route('country');

    return [
        'name' => [
            'required',
            'string',
            'max:255',
            Rule::unique('countries', 'name')->ignore($id),
        ],

         'country_image_path' => [
                'nullable',
                'file',
                'mimetypes:image/*',
                'max:400', // 1MB
            ],
    ];
}

}
