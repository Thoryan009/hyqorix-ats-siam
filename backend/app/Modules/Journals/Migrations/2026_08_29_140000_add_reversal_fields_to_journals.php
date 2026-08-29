<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->timestamp('reversed_at')->nullable()->after('status');
            $table->foreignId('reversed_by')->nullable()->after('reversed_at')->constrained('users')->nullOnDelete();
            $table->foreignId('reversal_journal_id')->nullable()->after('reversed_by')->constrained('journals')->nullOnDelete();
            $table->foreignId('reverses_journal_id')->nullable()->after('reversal_journal_id')->constrained('journals')->nullOnDelete();
        });

        DB::statement("
            ALTER TABLE journals
            MODIFY COLUMN status ENUM(
                'draft',
                'pending_approval',
                'approved',
                'returned',
                'posted',
                'reversed'
            ) NOT NULL DEFAULT 'posted'
        ");
    }

    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reverses_journal_id');
            $table->dropConstrainedForeignId('reversal_journal_id');
            $table->dropConstrainedForeignId('reversed_by');
            $table->dropColumn('reversed_at');
        });

        DB::statement("
            ALTER TABLE journals
            MODIFY COLUMN status ENUM(
                'draft',
                'pending_approval',
                'approved',
                'returned',
                'posted'
            ) NOT NULL DEFAULT 'posted'
        ");
    }
};
