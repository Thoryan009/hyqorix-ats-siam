<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');

            $table->string('bill_no')->unique();

            $table->decimal('amount', 12, 2);
            $table->enum('status', [
                'invoice-generated',
                'invoice-sent',
                'paid',
                'cancelled'
            ])->default('invoice-generated');

            $table->timestamps();

            // Foreign keys
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->onDelete('cascade');


        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_transactions');
    }
};
