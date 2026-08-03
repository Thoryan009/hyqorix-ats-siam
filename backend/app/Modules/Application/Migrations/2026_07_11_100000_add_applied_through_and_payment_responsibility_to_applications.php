<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('applied_through')->default('agent')->after('job_list_id');
            $table->json('payment_responsibility')->nullable()->after('applied_through');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign('applications_agent_id_fk');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->unsignedBigInteger('agent_id')->nullable()->change();
            $table->foreign('agent_id', 'applications_agent_id_fk')
                ->references('id')
                ->on('agents')
                ->nullOnDelete();
        });

        DB::table('applications')->whereNull('agent_id')->update([
            'applied_through' => 'direct_candidate',
        ]);

        DB::table('applications')->whereNotNull('agent_id')->update([
            'applied_through' => 'agent',
        ]);

        DB::table('applications')
            ->whereNull('payment_responsibility')
            ->update([
                'payment_responsibility' => json_encode(['agent']),
            ]);
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign('applications_agent_id_fk');
        });

        DB::table('applications')
            ->whereNull('agent_id')
            ->update(['agent_id' => DB::table('agents')->min('id')]);

        Schema::table('applications', function (Blueprint $table) {
            $table->unsignedBigInteger('agent_id')->nullable(false)->change();
            $table->foreign('agent_id', 'applications_agent_id_fk')
                ->references('id')
                ->on('agents')
                ->cascadeOnDelete();
            $table->dropColumn(['applied_through', 'payment_responsibility']);
        });
    }
};
