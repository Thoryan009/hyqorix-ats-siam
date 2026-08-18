<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_account_type_transactions', function (Blueprint $table) {
            $table->string('transaction_no', 32)->nullable()->unique()->after('id');
        });

        DB::table('finance_account_type_transactions')
            ->orderBy('id')
            ->select('id')
            ->chunkById(500, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('finance_account_type_transactions')
                        ->where('id', $row->id)
                        ->update([
                            'transaction_no' => sprintf('TXN-%06d', (int) $row->id),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('finance_account_type_transactions', function (Blueprint $table) {
            $table->dropUnique(['transaction_no']);
            $table->dropColumn('transaction_no');
        });
    }
};
