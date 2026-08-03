<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_mails', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->string('bill_no')->unique();
            
            $table->unsignedBigInteger('client_id');

            // Mail fields
            $table->string('to_mail');
            $table->string('subject');
            $table->longText('body');
            $table->string('invoice_path')->nullable();

            $table->timestamps();



            $table->foreign('client_id')
                  ->references('id')
                  ->on('clients')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_mails');
    }
};
