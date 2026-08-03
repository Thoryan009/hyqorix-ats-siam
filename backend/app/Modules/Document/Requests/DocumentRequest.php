<?php

namespace App\Modules\Document\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categories = config('document.categories', []);
        $isUpdate = (bool) $this->route('document');

        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in($categories)],
            'document_file' => [
                $isUpdate ? 'nullable' : 'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,webp,doc,docx',
                'max:2048',
            ],
        ];
    }
}
