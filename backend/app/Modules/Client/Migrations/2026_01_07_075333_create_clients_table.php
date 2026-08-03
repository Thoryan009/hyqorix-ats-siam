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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('client_id')->unique();

            $table->unsignedBigInteger('country_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('country_id', 'clients_country_id_fk')->references('id')->on('countries')->onDelete('cascade');
            $table->foreign('user_id', 'clients_user_id_fk')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by', 'clients_created_by_fk')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by', 'clients_updated_by_fk')->references('id')->on('users')->onDelete('set null');

            // Composite index
            $table->index(['country_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
