<?php

namespace App\Modules\Application\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        return [
            'ids' => ['required', 'array', 'min:1', 'exists:transactions,id'],
            'client_id' => ['required', 'exists:clients,id'],
        ];
    }
}
