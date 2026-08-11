<?php

namespace App\Modules\Setting\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
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
            'software_name' => [
                'required',
                'string',
                'max:255',
            ],
            'company_name' => [
                'required',
                'string',
                'max:255',
            ],
            'company_no' => [
                'nullable',
                'string',
                'max:255',
            ],
            'company_no_active' => [
                'nullable',
                'boolean',
            ],
            'company_phone' => [
                'required',
                'string',
                'max:50',
            ],
            'company_email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'backup_email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'expiry_report_notify_department_id' => [
                'nullable',
                'integer',
                'exists:departments,id',
            ],
            'tasheer_appointment_email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'company_address' => [
                'nullable',
                'string',
                'max:500',
            ],
            'company_logo_path' => [
                'nullable',
                'string',
                'max:255',
            ],
            'login_background_image_path' => [
                'nullable',
                'string',
                'max:255',
            ],
            'company_logo_file' => $this->fileRule,
            'login_background_image_file' => $this->fileRule,
            'fav_icon_file' => $this->fileRule,
            'primary_color' => [
                'nullable',
                'string',
                'regex:/^#([A-Fa-f0-9]{6})$/',
            ],
        ];
    }
}
