<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_income_collections', function (Blueprint $table) {
            $table->unsignedBigInteger('settles_income_collection_id')
                ->nullable()
                ->after('finance_account_type_transaction_id');

            $table->foreign('settles_income_collection_id', 'fic_settles_collection_fk')
                ->references('id')
                ->on('finance_income_collections')
                ->nullOnDelete();

            $table->index(['settles_income_collection_id'], 'fic_settles_collection_idx');
        });
    }

    public function down(): void
    {
        Schema::table('finance_income_collections', function (Blueprint $table) {
            $table->dropForeign('fic_settles_collection_fk');
            $table->dropIndex('fic_settles_collection_idx');
            $table->dropColumn('settles_income_collection_id');
        });
    }
};
