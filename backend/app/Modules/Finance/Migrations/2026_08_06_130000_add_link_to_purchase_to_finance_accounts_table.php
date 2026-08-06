<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_accounts', function (Blueprint $table) {
            $table->boolean('link_to_purchase')
                ->default(false)
                ->after('metadata');
        });
    }

    public function down(): void
    {
        Schema::table('finance_accounts', function (Blueprint $table) {
            $table->dropColumn('link_to_purchase');
        });
    }
};
