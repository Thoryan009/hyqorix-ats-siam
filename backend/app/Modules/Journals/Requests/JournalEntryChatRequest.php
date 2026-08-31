<?php

namespace App\Modules\Journals\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JournalEntryChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'messages' => ['required', 'array', 'min:1', 'max:24'],
            'messages.*.role' => ['required', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:4000'],
        ];
    }

    public function chatMessages(): array
    {
        return collect($this->input('messages', []))
            ->map(function ($message) {
                $message = is_array($message) ? $message : [];

                return [
                    'role' => (string) ($message['role'] ?? 'user'),
                    'content' => trim((string) ($message['content'] ?? '')),
                ];
            })
            ->filter(fn ($message) => $message['content'] !== '')
            ->values()
            ->all();
    }
}
