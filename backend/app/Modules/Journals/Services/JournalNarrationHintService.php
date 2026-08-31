<?php

namespace App\Modules\Journals\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class JournalNarrationHintService
{
    public function generate(array $context): array
    {
        $hints = $this->generateWithOpenAi($context);

        if ($hints !== []) {
            return $hints;
        }

        return $this->fallbackHints($context);
    }

    private function generateWithOpenAi(array $context): array
    {
        $apiKey = config('services.openai.api_key');
        $model = config('services.openai.model', 'gpt-4o-mini');
        $baseUrl = rtrim(config('services.openai.base_url', 'https://api.openai.com/v1'), '/');

        if (blank($apiKey)) {
            return [];
        }

        try {
            $response = Http::acceptJson()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                ])
                ->timeout(20)
                ->post("{$baseUrl}/chat/completions", [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an expert accounting assistant for an overseas recruitment agency ATS. Suggest concise, professional journal narrations suitable for accounting vouchers.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $this->buildPrompt($context),
                        ],
                    ],
                    'temperature' => 0.35,
                    'max_tokens' => 320,
                    'response_format' => ['type' => 'json_object'],
                ]);

            if ($response->failed()) {
                return [];
            }

            $content = $response->json('choices.0.message.content');
            if (!is_string($content) || trim($content) === '') {
                return [];
            }

            $decoded = json_decode($content, true);
            if (!is_array($decoded)) {
                return [];
            }

            $hints = $decoded['hints'] ?? [];

            return collect($hints)
                ->filter(fn ($hint) => is_string($hint) && trim($hint) !== '')
                ->map(fn ($hint) => Str::limit(trim($hint), 220, ''))
                ->unique()
                ->take(4)
                ->values()
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    private function buildPrompt(array $context): string
    {
        $lines = collect($context['lines'] ?? [])
            ->map(function ($line) {
                $parts = array_filter([
                    $line['account_label'] ?? null,
                    ($line['debit'] ?? 0) > 0 ? 'Dr ' . number_format((float) $line['debit'], 2) : null,
                    ($line['credit'] ?? 0) > 0 ? 'Cr ' . number_format((float) $line['credit'], 2) : null,
                    $line['cost_type_label'] ?? null,
                ]);

                return $parts !== [] ? '- ' . implode(' | ', $parts) : null;
            })
            ->filter()
            ->implode("\n");

        $details = array_filter([
            'Voucher date' => $context['voucher_date'] ?? null,
            'Transaction type' => $context['transaction_type_label'] ?: ($context['transaction_type'] ?? null),
            'Reference no' => $context['reference_no'] ?? null,
            'Party' => $context['party_label'] ?? null,
            'Project' => $context['project_label'] ?? null,
        ]);

        $summary = collect($details)
            ->map(fn ($value, $label) => "{$label}: {$value}")
            ->implode("\n");

        return <<<PROMPT
Based on this journal entry draft, suggest exactly 4 short narration options (one sentence each, professional accounting tone).

Journal details:
{$summary}

Journal lines:
{$lines}

Return JSON only in this shape:
{"hints":["hint 1","hint 2","hint 3","hint 4"]}
PROMPT;
    }

    private function fallbackHints(array $context): array
    {
        $transaction = $context['transaction_type_label'] ?: ($context['transaction_type'] ?: 'Journal entry');
        $party = $context['party_label'] ?? '';
        $reference = $context['reference_no'] ?? '';
        $date = $context['voucher_date'] ?? '';

        $amountLine = collect($context['lines'] ?? [])->first(function ($line) {
            return ($line['debit'] ?? 0) > 0 || ($line['credit'] ?? 0) > 0;
        });

        $amount = 0.0;
        if (is_array($amountLine)) {
            $amount = max((float) ($amountLine['debit'] ?? 0), (float) ($amountLine['credit'] ?? 0));
        }

        $account = is_array($amountLine) ? ($amountLine['account_label'] ?? '') : '';
        $amountText = $amount > 0 ? number_format($amount, 2) : null;

        $hints = [
            trim("{$transaction}" . ($party ? " for {$party}" : '') . ($date ? " dated {$date}" : '') . '.'),
            trim("Being amount" . ($amountText ? " of {$amountText}" : '') . ($account ? " booked to {$account}" : '') . ($reference ? " against ref {$reference}" : '') . '.'),
            trim("Accounting entry recorded" . ($party ? " for {$party}" : '') . ($reference ? " (Ref: {$reference})" : '') . '.'),
            trim(($account ? "{$account} transaction" : 'Journal transaction') . ($amountText ? " amount {$amountText}" : '') . ($date ? " on {$date}" : '') . '.'),
        ];

        return collect($hints)
            ->filter(fn ($hint) => trim($hint) !== '' && trim($hint) !== '.')
            ->unique()
            ->take(4)
            ->values()
            ->all();
    }
}
