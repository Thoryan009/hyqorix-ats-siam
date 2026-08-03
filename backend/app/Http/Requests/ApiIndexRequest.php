<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApiIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:500',
            'search' => 'nullable|string',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ];
    }

    public function filters(): array
    {
        return [
            'page' => (int) $this->get('page', 1),
            'per_page' => (int) $this->get('per_page', 10),
            'search' => $this->get('search'),
            'from_date' => $this->get('from_date'),
            'to_date' => $this->get('to_date'),

        ];
    }
}
