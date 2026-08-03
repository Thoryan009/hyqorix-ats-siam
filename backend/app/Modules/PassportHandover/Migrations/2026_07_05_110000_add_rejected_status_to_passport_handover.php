<?php

use App\Modules\PassportHandover\Models\PassportHandover;
use App\Modules\PassportHandover\Models\PassportHandoverItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE passport_handover_items MODIFY COLUMN status ENUM('handed_over', 'collected', 'rejected') NOT NULL DEFAULT 'handed_over'"
        );

        DB::statement(
            "ALTER TABLE passport_handovers MODIFY COLUMN status ENUM('handed_over', 'partially_collected', 'collected', 'rejected') NOT NULL DEFAULT 'handed_over'"
        );

        Schema::table('passport_handover_items', function (Blueprint $table) {
            $table->date('reject_date')->nullable()->after('return_date');
            $table->foreignId('rejected_by')
                ->nullable()
                ->after('collected_by_user_id')
                ->constrained('employees')
                ->nullOnDelete();
            $table->foreignId('rejected_by_user_id')
                ->nullable()
                ->after('rejected_by')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('passport_handover_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rejected_by_user_id');
            $table->dropConstrainedForeignId('rejected_by');
            $table->dropColumn('reject_date');
        });

        PassportHandoverItem::query()
            ->where('status', 'rejected')
            ->update(['status' => 'handed_over']);

        PassportHandover::query()
            ->where('status', 'rejected')
            ->update(['status' => 'handed_over']);

        DB::statement(
            "ALTER TABLE passport_handover_items MODIFY COLUMN status ENUM('handed_over', 'collected') NOT NULL DEFAULT 'handed_over'"
        );

        DB::statement(
            "ALTER TABLE passport_handovers MODIFY COLUMN status ENUM('handed_over', 'partially_collected', 'collected') NOT NULL DEFAULT 'handed_over'"
        );
    }
};
