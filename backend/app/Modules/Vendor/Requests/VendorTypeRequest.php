<?php

namespace App\Modules\Vendor\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VendorTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('status')) {
            $this->merge([
                'status' => $this->normalizeStatus($this->input('status')),
            ]);
        }
    }

    public function rules(): array
    {
        $vendorTypeId = $this->route('vendorType')?->id ?? $this->input('id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vendor_types', 'name')->ignore($vendorTypeId),
            ],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    private function normalizeStatus(?string $status): string
    {
        return strtolower((string) $status) === 'inactive' ? 'inactive' : 'active';
    }
}
