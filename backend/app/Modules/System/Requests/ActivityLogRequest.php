<?php

namespace App\Modules\System\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActivityLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'action' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}
