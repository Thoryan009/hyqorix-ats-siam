<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->tinyInteger('send_credentials')->default(1)->after('updated_by');
            $table->string('image_path')->nullable()->after('send_credentials');
            $table->string('username')->nullable()->unique()->after('image_path');

        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['send_credentials', 'image_path', 'username']);
        });
    }
};
