<?php

namespace App\Modules\Journals\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class JournalEntryChatService
{
    public function chat(array $messages): string
    {
        $apiKey = config('services.openai.api_key');
        $model = config('services.openai.model', 'gpt-4o-mini');
        $baseUrl = rtrim(config('services.openai.base_url', 'https://api.openai.com/v1'), '/');

        if (blank($apiKey)) {
            throw new RuntimeException('OpenAI API key is not configured.');
        }

        $payloadMessages = array_merge(
            [['role' => 'system', 'content' => $this->systemPrompt()]],
            $messages,
        );

        $response = Http::acceptJson()
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])
            ->timeout(45)
            ->post("{$baseUrl}/chat/completions", [
                'model' => $model,
                'messages' => $payloadMessages,
                'temperature' => 0.35,
                'max_tokens' => 1200,
            ]);

        if ($response->failed()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new RuntimeException(is_string($error) ? $error : 'OpenAI request failed.');
        }

        $reply = $response->json('choices.0.message.content');

        if (!is_string($reply) || trim($reply) === '') {
            throw new RuntimeException('OpenAI returned an empty response.');
        }

        return trim($reply);
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
You are an expert accounting assistant for an overseas manpower/recruitment agency.

Help users prepare journal vouchers (double-entry bookkeeping). When asked about a transaction or narration:

1. Briefly explain the business context in plain language.
2. Suggest a balanced journal entry with clear debit and credit lines.
3. Use typical recruitment-agency account names (e.g. Cash, Bank, Accounts Receivable, Client Advance, Agent Payable, Recruitment Revenue, Medical Expense, Ticket Expense, BMET Expense, Visa Expense, Salary Expense, Office Rent, Accrued Expenses, Owner Capital, Loan Payable).
4. Show amounts as placeholders like "XXX" if the user did not provide amounts.
5. Include a professional narration line they can paste into the voucher.
6. Keep answers structured with short headings and bullet points.
7. If the user only provides a narration label, infer the likely debit/credit pattern used in recruitment agencies.

Do not invent legal or tax advice. Focus on practical journal entry guidance.
PROMPT;
    }
}
