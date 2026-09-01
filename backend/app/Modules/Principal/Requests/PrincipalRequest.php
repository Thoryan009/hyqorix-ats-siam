<?php

namespace App\Modules\Principal\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Modules\Principal\Models\Principal;

class PrincipalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // $id = $this->route('principal');
        $id = $this->id; // string/int from URL

        /** @var Principal|null $principal */
        $principal = $id ? Principal::find($id) : null;

        $principalPk = $principal?->id;
        $userId = $principal?->user_id;



        return [
            // ───── User fields ─────
            'organization_name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],

            'contact_no' => [
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

            'password' => [
                $principal ? 'nullable' : 'required',
                'string',
                'min:8',
            ],


            // ───── Principal main fields ─────


            'address' => [
                'nullable',
                'string',
                'max:255',
            ],


            'contact_person_name' => [
                'required',
                'string',
                'max:255',
            ],

            'country_id' => [
                'nullable',
                'integer',
                'exists:countries,id',
            ],

            'designation' => [
                'required',
                'string',
                'max:255',
            ],

            'contact_person_no' => [
                'nullable',
                'string',
                'max:20',
            ],

            'role_id' => [
                'required',
                'integer',
                'exists:roles,id',
            ],

            'send_notification' => [
                'required',
                Rule::in([1, 0]),
            ],

            'status' => [
                'required',
                Rule::in([0, 1]),
            ],
            'create_party_account' => ['nullable', Rule::in([0, 1, '0', '1', true, false])],
        ];
    }
}
