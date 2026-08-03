<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE applications
            MODIFY sex ENUM('male', 'female', 'other') NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE applications
            MODIFY sex ENUM('Male', 'Female', 'Other') NULL
        ");
    }
};
