<?php

namespace App\Modules\PassportHandover\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CollectPassportHandoverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $passportHandoverId = $this->route('passportHandover')?->id ?? $this->route('passport_handover');

        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => [
                'required',
                'integer',
                Rule::exists('passport_handover_items', 'id')->where(
                    fn ($query) => $query->where('passport_handover_id', $passportHandoverId)
                ),
            ],
            'items.*.action' => ['required', Rule::in(['collect', 'reject'])],
            'items.*.return_date' => ['nullable', 'date'],
            'items.*.reject_date' => ['nullable', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach ($this->input('items', []) as $index => $item) {
                $action = $item['action'] ?? null;

                if ($action === 'collect' && empty($item['return_date'])) {
                    $validator->errors()->add(
                        "items.{$index}.return_date",
                        'Return date is required when collecting a passport.'
                    );
                }

                if ($action === 'reject' && empty($item['reject_date'])) {
                    $validator->errors()->add(
                        "items.{$index}.reject_date",
                        'Reject date is required when rejecting a passport.'
                    );
                }
            }
        });
    }
}
