<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('job_lists')) {
            return;
        }

        if (
            Schema::hasColumn('job_lists', 'price_usd')
            && !Schema::hasColumn('job_lists', 'client_commission_per_candidate')
        ) {
            Schema::table('job_lists', function (Blueprint $table) {
                $table->renameColumn('price_usd', 'client_commission_per_candidate');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('job_lists')) {
            return;
        }

        if (
            Schema::hasColumn('job_lists', 'client_commission_per_candidate')
            && !Schema::hasColumn('job_lists', 'price_usd')
        ) {
            Schema::table('job_lists', function (Blueprint $table) {
                $table->renameColumn('client_commission_per_candidate', 'price_usd');
            });
        }
    }
};
