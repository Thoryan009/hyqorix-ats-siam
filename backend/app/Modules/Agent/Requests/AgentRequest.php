<?php

namespace App\Modules\Agent\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Modules\Agent\Models\Agent;

class AgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->id; // string/int from URL
        // \Log::info("AgentRequest rules() called with ID: $id");

        /** @var Agent|null $agent */
        $agent = $id ? Agent::find($id) : null;

        $agentPk = $agent?->id;
        $userId = $agent?->user_id;



        return [
            // ───── User fields ─────
            'name' => ['required', 'string', 'max:255'],

            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],

            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($userId)],
            'whatsapp_no' => ['nullable', 'string', 'max:20', Rule::unique('users', 'whatsapp_no')->ignore($userId)],

            'agent_image_path' => [
                'nullable',
                'file',
                'mimetypes:image/*',
                'max:400', // 1MB
            ],

            'phone2' => ['nullable', 'string', 'max:20', Rule::unique('agents', 'phone2')->ignore($agentPk)],

            'stuff_name' => ['nullable', 'string', 'max:255'],

            'stuff_phone' => ['nullable', 'string', 'max:20', Rule::unique('agents', 'stuff_phone')->ignore($agentPk)],

            'manager_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            // 'password' => [
            //     $agent ? 'nullable' : 'required',
            //     'string',
            //     'min:8',
            // ],
             'password' => [$agentPk ? 'nullable' : 'required', 'string', 'min:8'],

            // ───── Agent fields ─────

            'address' => ['nullable', 'string', 'max:500'],

            'role_id' => ['required', 'integer', 'exists:roles,id'],

            'nid_no' => ['nullable', 'integer', Rule::unique('agents', 'nid_no')->ignore($agentPk)],
            'status' => ['required', Rule::in([0, 1])],
            'create_party_account' => ['nullable', Rule::in([0, 1, '0', '1', true, false])],
        ];
    }

}
