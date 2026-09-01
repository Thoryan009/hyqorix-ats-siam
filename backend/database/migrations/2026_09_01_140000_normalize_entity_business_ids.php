<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $this->normalizeTableIds('clients', 'client_id', fn (string $old) => $this->formatClientId($old));
            $this->normalizeTableIds('agents', 'agent_id', fn (string $old) => $this->formatAgentId($old));
            $this->normalizeTableIds('principals', 'principal_id', fn (string $old) => $this->formatPrincipalId($old));
            $this->normalizeTableIds('vendors', 'vendor_id', fn (string $old) => $this->formatVendorId($old));
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            $this->normalizeTableIds('clients', 'client_id', fn (string $old) => $this->revertClientId($old));
            $this->normalizeTableIds('agents', 'agent_id', fn (string $old) => $this->revertAgentId($old));
            $this->normalizeTableIds('principals', 'principal_id', fn (string $old) => $this->revertPrincipalId($old));
            $this->normalizeTableIds('vendors', 'vendor_id', fn (string $old) => $this->revertVendorId($old));
        });
    }

    private function normalizeTableIds(string $table, string $column, callable $formatter): void
    {
        $rows = DB::table($table)->orderBy('id')->get(['id', $column]);

        foreach ($rows as $row) {
            DB::table($table)->where('id', $row->id)->update([
                $column => '_tmp_'.$row->id,
            ]);
        }

        foreach ($rows as $row) {
            $oldValue = (string) $row->{$column};
            $newValue = $formatter($oldValue);

            DB::table($table)->where('id', $row->id)->update([
                $column => $newValue,
            ]);
        }
    }

    private function extractTrailingNumber(string $value): ?int
    {
        if (! preg_match('/(\d+)\s*$/', trim($value), $matches)) {
            return null;
        }

        $number = (int) $matches[1];

        return $number > 0 ? $number : null;
    }

    private function formatClientId(string $old): string
    {
        $number = $this->extractTrailingNumber($old);

        return $number ? 'CL'.str_pad((string) $number, 3, '0', STR_PAD_LEFT) : strtoupper(str_replace('-', '', trim($old)));
    }

    private function formatAgentId(string $old): string
    {
        $number = $this->extractTrailingNumber($old);

        return $number ? 'AG'.str_pad((string) $number, 3, '0', STR_PAD_LEFT) : strtoupper(str_replace('-', '', trim($old)));
    }

    private function formatPrincipalId(string $old): string
    {
        $number = $this->extractTrailingNumber($old);

        return $number ? 'PR'.str_pad((string) $number, 3, '0', STR_PAD_LEFT) : strtoupper(str_replace('-', '', trim($old)));
    }

    private function formatVendorId(string $old): string
    {
        $number = $this->extractTrailingNumber($old);

        return $number ? 'VND'.str_pad((string) $number, 3, '0', STR_PAD_LEFT) : strtoupper(str_replace('-', '', trim($old)));
    }

    private function revertClientId(string $old): string
    {
        $number = $this->extractTrailingNumber($old);

        return $number ? 'CL-'.str_pad((string) $number, 3, '0', STR_PAD_LEFT) : $old;
    }

    private function revertAgentId(string $old): string
    {
        $number = $this->extractTrailingNumber($old);

        return $number ? 'AGENT-'.str_pad((string) $number, 3, '0', STR_PAD_LEFT) : $old;
    }

    private function revertPrincipalId(string $old): string
    {
        $number = $this->extractTrailingNumber($old);

        return $number ? 'PRINCIPAL-'.str_pad((string) $number, 3, '0', STR_PAD_LEFT) : $old;
    }

    private function revertVendorId(string $old): string
    {
        $number = $this->extractTrailingNumber($old);

        return $number ? 'VND-'.str_pad((string) $number, 3, '0', STR_PAD_LEFT) : $old;
    }
};
