<?php

namespace App\Modules\Journals\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JournalApproveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $comment = trim((string) $this->input('manager_comment', ''));

        $this->merge([
            'manager_comment' => $comment !== '' ? $comment : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'manager_comment' => ['required', 'string', 'max:2000'],
        ];
    }
}
