<?php

namespace App\Modules\Vendor\Requests;

use App\Modules\Vendor\Models\Vendor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->id;
        $vendor = $id ? Vendor::find($id) : null;
        $userId = $vendor?->user_id;

        return [
            'organization_name' => ['required', 'string', 'max:255'],
            'vendor_type' => [
                'required',
                'string',
                'max:100',
                Rule::exists('vendor_types', 'code'),
            ],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->ignore($userId),
            ],
            'whatsapp_no' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'whatsapp_no')->ignore($userId),
            ],
            'password' => [$vendor ? 'nullable' : 'required', 'string', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['required', Rule::in([0, 1])],
            'send_notification' => ['required', Rule::in([0, 1])],
            'vendor_image_path' => [
                'nullable',
                'file',
                'mimetypes:image/*',
                'max:400',
            ],
            'create_party_account' => ['nullable', Rule::in([0, 1, '0', '1', true, false])],
        ];
    }
}
