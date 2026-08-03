<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ];
    }

    public function filters(): array
    {
        return [
            'from_date' => $this->get('from_date'),
            'to_date' => $this->get('to_date'),

        ];
    }
}

