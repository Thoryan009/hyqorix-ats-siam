<?php

namespace App\Modules\JobList\Helpers;

use Illuminate\Database\Eloquent\Builder;

class JobListPayerHelper
{
    public const AGENT = 'agent';
    public const CLIENT = 'client';
    public const CANDIDATE = 'candidate';

    public const ALL = [
        self::AGENT,
        self::CLIENT,
        self::CANDIDATE,
    ];

    public static function normalize(mixed $payer): array
    {
        if (is_array($payer)) {
            return array_values(
                array_unique(
                    array_filter(
                        array_map(static fn ($value) => strtolower(trim((string) $value)), $payer)
                    )
                )
            );
        }

        if (is_string($payer) && $payer !== '') {
            $decoded = json_decode($payer, true);
            if (is_array($decoded)) {
                return self::normalize($decoded);
            }

            if ($payer === 'both') {
                return [self::CLIENT, self::CANDIDATE];
            }

            return [strtolower($payer)];
        }

        return [];
    }

    public static function has(mixed $payer, string $type): bool
    {
        return in_array(strtolower($type), self::normalize($payer), true);
    }

    public static function label(mixed $payer): string
    {
        $labels = [
            self::AGENT => 'Agent',
            self::CLIENT => 'Client',
            self::CANDIDATE => 'Candidate',
        ];

        return collect(self::normalize($payer))
            ->map(fn (string $value) => $labels[$value] ?? ucfirst($value))
            ->implode(', ');
    }

    public static function usesUsdFormatting(mixed $payer): bool
    {
        $payers = self::normalize($payer);

        return in_array(self::CLIENT, $payers, true)
            && !in_array(self::CANDIDATE, $payers, true)
            && !in_array(self::AGENT, $payers, true);
    }

    public static function usesCombinedAmounts(mixed $payer): bool
    {
        $payers = self::normalize($payer);

        return in_array(self::CLIENT, $payers, true)
            && in_array(self::CANDIDATE, $payers, true);
    }

    /**
     * Resolve payers from application.payment_responsibility.
     */
    public static function resolveForApplication(mixed $application): array
    {
        return self::normalize($application->payment_responsibility ?? null);
    }

    /**
     * Filter applications by payment_responsibility.
     */
    public static function scopeWhereApplicationResponsible(Builder $query, string $type): Builder
    {
        return $query->whereJsonContains('payment_responsibility', $type);
    }

    public static function filterOptions(): array
    {
        return [
            ['id' => self::AGENT, 'name' => 'Agent'],
            ['id' => self::CLIENT, 'name' => 'Client'],
            ['id' => self::CANDIDATE, 'name' => 'Candidate'],
        ];
    }
}
