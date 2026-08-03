<?php

namespace App\Modules\Application\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmbassyListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'submit_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.list_type' => ['required', Rule::in(['restamping', 'new_stamping', 'cancellation'])],
            'items.*.application_id' => ['nullable', 'integer', 'exists:applications,id'],
            'items.*.passport_no' => ['required', 'string', 'max:255'],
            'items.*.sort_order' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
