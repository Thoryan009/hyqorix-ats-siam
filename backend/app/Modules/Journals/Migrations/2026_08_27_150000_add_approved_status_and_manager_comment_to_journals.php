<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE journals
            MODIFY COLUMN status ENUM(
                'draft',
                'pending_approval',
                'approved',
                'posted'
            ) NOT NULL DEFAULT 'posted'
        ");

        Schema::table('journals', function (Blueprint $table) {
            $table->text('manager_comment')->nullable()->after('narration');
        });
    }

    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->dropColumn('manager_comment');
        });

        DB::statement("
            ALTER TABLE journals
            MODIFY COLUMN status ENUM(
                'draft',
                'pending_approval',
                'posted'
            ) NOT NULL DEFAULT 'posted'
        ");
    }
};
