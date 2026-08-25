<?php

namespace App\Modules\Parties\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PartyTypeBulkDeleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:party_types,id'],
        ];
    }
}
