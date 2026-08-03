<?php

namespace App\Modules\Application\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkStatusUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['required', 'integer', 'exists:applications,id'],
            'status' => ['required', 'string', 'in:hiring_list,rejected_list,waiting_list,application_list,short_list'],
        ];
    }
}
