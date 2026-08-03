<?php

namespace App\Modules\Setting\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmbassyRequest extends FormRequest
{

    private array $fileRule = [
        'nullable',
        'file',
        'mimetypes:image/*',
        'max:400', // 1MB (KB)
    ];
    public function authorize(): bool
    {
        return true; // move policy logic here later
    }

    public function rules(): array
    {

        return [
            'embassy_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'embassy_address' => [
                'nullable',
                'string',
                'max:255',
            ],
            'embassy_company_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'company_rl' => [
                'nullable',
                'string',
                'max:255',
            ],

        ];
    }
}
