<?php

namespace App\Modules\Parties\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PartyTypeMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $modules = array_keys(config('parties.source_modules', []));

        return [
            'mappings' => ['required', 'array'],
            'mappings.*.source_module' => ['required', 'string', Rule::in($modules)],
            'mappings.*.party_type_id' => ['nullable', 'integer', 'exists:party_types,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $mappings = $this->input('mappings', []);
            $partyTypeIds = [];

            foreach ($mappings as $index => $mapping) {
                $partyTypeId = $mapping['party_type_id'] ?? null;

                if ($partyTypeId === null || $partyTypeId === '') {
                    continue;
                }

                $partyTypeId = (int) $partyTypeId;

                if (isset($partyTypeIds[$partyTypeId])) {
                    $validator->errors()->add(
                        "mappings.{$index}.party_type_id",
                        'Each party type can only be linked to one management module.'
                    );
                }

                $partyTypeIds[$partyTypeId] = true;
            }
        });
    }
}
