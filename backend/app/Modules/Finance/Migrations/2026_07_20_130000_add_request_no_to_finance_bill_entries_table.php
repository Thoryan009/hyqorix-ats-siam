<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_bill_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('finance_bill_entries', 'request_no')) {
                $table->string('request_no')->nullable()->after('batch_ref');
                $table->index(['request_no'], 'fbill_request_no_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('finance_bill_entries', function (Blueprint $table) {
            if (Schema::hasColumn('finance_bill_entries', 'request_no')) {
                $table->dropIndex('fbill_request_no_idx');
                $table->dropColumn('request_no');
            }
        });
    }
};
