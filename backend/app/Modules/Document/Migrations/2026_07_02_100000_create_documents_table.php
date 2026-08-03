<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_no')->unique();
            $table->string('name');
            $table->enum('category', [
                'License',
                'Profile',
                'NID',
                'Passport Copy',
                'Picture',
                'Certificate',
                'Other',
            ]);
            $table->string('path');
            $table->timestamps();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable()->index();

            $table->foreign('created_by', 'documents_created_by_foreign')
                ->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by', 'documents_updated_by_foreign')
                ->references('id')->on('users')->onDelete('set null');

            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
