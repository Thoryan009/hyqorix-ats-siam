<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE users
            MODIFY COLUMN type ENUM(
                'super_admin',
                'admin',
                'client',
                'recruiter',
                'accountant',
                'agent',
                'employee',
                'principal',
                'vendor'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE users
            MODIFY COLUMN type ENUM(
                'super_admin',
                'admin',
                'client',
                'recruiter',
                'accountant',
                'agent',
                'employee',
                'principal'
            ) NOT NULL
        ");
    }
};
