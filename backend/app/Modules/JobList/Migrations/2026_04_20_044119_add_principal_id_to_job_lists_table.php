<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('job_lists', function (Blueprint $table) {
            $table->unsignedBigInteger('principal_id')->nullable()->index()->after('work_order_id');
            $table->foreign('principal_id', 'job_lists_principal_id_fk')->references('id')->on('principals')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_lists', function (Blueprint $table) {
            $table->dropForeign('job_lists_principal_id_fk');
            $table->dropIndex('job_lists_principal_id_index');
            $table->dropColumn('principal_id');
        });
    }
};
