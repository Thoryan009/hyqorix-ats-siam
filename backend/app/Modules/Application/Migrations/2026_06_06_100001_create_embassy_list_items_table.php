<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('embassy_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('embassy_list_id')->constrained('embassy_lists')->cascadeOnDelete();
            $table->foreignId('application_id')->nullable()->constrained('applications')->nullOnDelete();
            $table->enum('list_type', ['restamping', 'new_stamping', 'cancellation']);
            $table->string('passport_no');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['embassy_list_id', 'passport_no'], 'embassy_list_items_list_passport_unique');
            $table->index(['passport_no', 'list_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('embassy_list_items');
    }
};
