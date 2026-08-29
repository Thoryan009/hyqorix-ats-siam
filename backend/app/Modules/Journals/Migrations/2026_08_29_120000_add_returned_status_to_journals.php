<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

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
                'returned',
                'posted'
            ) NOT NULL DEFAULT 'posted'
        ");
    }

    public function down(): void
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
    }
};
