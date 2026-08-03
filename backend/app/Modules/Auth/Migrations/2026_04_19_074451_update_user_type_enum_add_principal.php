<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
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
                'principal'
            ) NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
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
                'employee'
            ) NOT NULL
        ");
    }
};