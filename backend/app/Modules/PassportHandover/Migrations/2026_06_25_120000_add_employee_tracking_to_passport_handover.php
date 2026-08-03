<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('passport_handovers', function (Blueprint $table) {
            $table->foreignId('handed_over_by')
                ->nullable()
                ->after('status')
                ->constrained('employees')
                ->nullOnDelete();
        });

        Schema::table('passport_handover_items', function (Blueprint $table) {
            $table->foreignId('handed_over_by')
                ->nullable()
                ->after('status')
                ->constrained('employees')
                ->nullOnDelete();

            $table->foreignId('collected_by')
                ->nullable()
                ->after('handed_over_by')
                ->constrained('employees')
                ->nullOnDelete();

            $table->dropColumn('collected_at');
        });
    }

    public function down(): void
    {
        Schema::table('passport_handover_items', function (Blueprint $table) {
            $table->timestamp('collected_at')->nullable()->after('status');
            $table->dropConstrainedForeignId('collected_by');
            $table->dropConstrainedForeignId('handed_over_by');
        });

        Schema::table('passport_handovers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('handed_over_by');
        });
    }
};
