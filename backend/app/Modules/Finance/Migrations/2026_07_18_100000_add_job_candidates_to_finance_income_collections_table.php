<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_income_collections', function (Blueprint $table) {
            $table->unsignedBigInteger('job_list_id')->nullable()->after('income_head_id');
            $table->string('job_code')->nullable()->after('job_list_id');
            $table->string('job_title')->nullable()->after('job_code');
            $table->string('client_name')->nullable()->after('job_title');
            $table->json('candidates')->nullable()->after('client_name');
        });
    }

    public function down(): void
    {
        Schema::table('finance_income_collections', function (Blueprint $table) {
            $table->dropColumn(['job_list_id', 'job_code', 'job_title', 'client_name', 'candidates']);
        });
    }
};
