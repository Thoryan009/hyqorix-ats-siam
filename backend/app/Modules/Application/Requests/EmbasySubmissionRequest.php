<?php

namespace App\Modules\Application\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmbasySubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        return [
            // application_id must be required when creating a new record (POST),
            // but optional on update (PUT/PATCH) since the relation already exists.
            'application_id' => $this->isMethod('post')
                ? ['required', 'exists:applications,id']
                : ['nullable', 'exists:applications,id'],
            'religion' => ['required', 'in:muslim,non-muslim'],
            'visa_profession_ar' => ['nullable', 'string'],
            'visa_profession_en' => ['nullable', 'string'],
            'visit_work_for_ar' => ['nullable', 'string'],
            'mofa_no' => ['nullable', 'string'],
            'police_clearance_no' => ['nullable', 'string'],
            'alwakala_no' => ['nullable', 'string'],
        ];
    }
}
