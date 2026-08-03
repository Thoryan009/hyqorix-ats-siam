<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_id')->unique();
            $table->string('organization_name');
            $table->string('contact_person')->nullable();
            $table->string('address')->nullable();
            $table->string('vendor_image_path')->nullable();
            $table->boolean('send_notification')->default(true);
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('user_id', 'vendors_user_id_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('created_by', 'vendors_created_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('updated_by', 'vendors_updated_by_fk')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
