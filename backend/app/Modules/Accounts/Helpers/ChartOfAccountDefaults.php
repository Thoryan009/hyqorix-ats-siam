<?php

namespace App\Modules\Accounts\Helpers;

class ChartOfAccountDefaults
{
    public static function financialStatementForType(?string $type): ?string
    {
        return match (trim((string) $type)) {
            'Asset', 'Contra Asset', 'Liability', 'Equity', 'Contra Equity' => 'Balance Sheet',
            'Revenue', 'Contra Revenue', 'Direct Cost A', 'Direct Cost B' => 'Gross Profit',
            'Other Operating Revenue', 'Other Income', 'Operating Expense', 'Finance Cost', 'Other Expense' => 'Income Statement',
            default => null,
        };
    }

    public static function normalBalanceForType(?string $type): ?string
    {
        return match (trim((string) $type)) {
            'Asset', 'Contra Equity', 'Contra Revenue', 'Direct Cost A', 'Direct Cost B',
            'Operating Expense', 'Finance Cost', 'Other Expense' => 'debit',
            'Contra Asset', 'Liability', 'Equity', 'Revenue', 'Other Operating Revenue', 'Other Income' => 'credit',
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function applyTypeDefaults(array $data): array
    {
        $type = $data['type'] ?? null;
        $statement = self::financialStatementForType($type);
        if ($statement !== null) {
            $data['financial_statement'] = $statement;
        }

        $normalBalance = self::normalBalanceForType($type);
        if ($normalBalance !== null) {
            $data['normal_balance'] = $normalBalance;
        }

        return $data;
    }
}
