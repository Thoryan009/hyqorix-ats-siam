<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('finance_bill_entries', 'receipt_path')) {
            return;
        }

        Schema::table('finance_bill_entries', function (Blueprint $table) {
            $table->text('receipt_path')->nullable()->change();
        });

        // Normalize legacy single-path strings into JSON arrays.
        DB::table('finance_bill_entries')
            ->whereNotNull('receipt_path')
            ->where('receipt_path', '!=', '')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $value = $row->receipt_path;
                    $decoded = json_decode($value, true);

                    if (is_array($decoded)) {
                        continue;
                    }

                    DB::table('finance_bill_entries')
                        ->where('id', $row->id)
                        ->update([
                            'receipt_path' => json_encode([$value]),
                        ]);
                }
            });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('finance_bill_entries', 'receipt_path')) {
            return;
        }

        DB::table('finance_bill_entries')
            ->whereNotNull('receipt_path')
            ->where('receipt_path', '!=', '')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $decoded = json_decode($row->receipt_path, true);
                    $first = is_array($decoded) ? ($decoded[0] ?? null) : $row->receipt_path;

                    DB::table('finance_bill_entries')
                        ->where('id', $row->id)
                        ->update([
                            'receipt_path' => $first,
                        ]);
                }
            });

        Schema::table('finance_bill_entries', function (Blueprint $table) {
            $table->string('receipt_path')->nullable()->change();
        });
    }
};
