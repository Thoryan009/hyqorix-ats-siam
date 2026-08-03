<?php

namespace App\Modules\Application\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendMailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        return [
            'to_mail' => ['nullable', 'email'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'bill_no' => ['required', 'string', 'exists:transactions,bill_no'],
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'invoice_path' => [
                'nullable',
                'file',
                'mimetypes:image/*,application/pdf',
                'max:1024'
            ],

        ];
    }
}
