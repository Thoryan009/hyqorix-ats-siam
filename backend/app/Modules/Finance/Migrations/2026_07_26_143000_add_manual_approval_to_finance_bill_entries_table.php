<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_bill_entries', function (Blueprint $table) {
            $table->boolean('is_manual_request')->default(false)->after('request_no');
            $table->unsignedBigInteger('manual_approval_manager_id')->nullable()->after('manager_approved_by');
            $table->string('manual_approval_manager_name')->nullable()->after('manual_approval_manager_id');
            $table->string('manual_approval_path')->nullable()->after('manual_approval_manager_name');
        });

        // Bills already sitting in Bills To Pay without a manager approval came from
        // the "Submit Manual Request" flow.
        DB::table('finance_bill_entries')
            ->whereIn('status', ['pending', 'approved'])
            ->whereNull('manager_approved_at')
            ->update(['is_manual_request' => true]);
    }

    public function down(): void
    {
        Schema::table('finance_bill_entries', function (Blueprint $table) {
            $table->dropColumn([
                'is_manual_request',
                'manual_approval_manager_id',
                'manual_approval_manager_name',
                'manual_approval_path',
            ]);
        });
    }
};
