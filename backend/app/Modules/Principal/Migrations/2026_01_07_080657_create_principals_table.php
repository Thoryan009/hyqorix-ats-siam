<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
   public function up(): void
   {
       Schema::create('principals', function (Blueprint $table) {
           $table->id();
           $table->string('principal_id')->unique();
           $table->string('address')->nullable();
           $table->string('contact_person_name');
           $table->string('designation');
           $table->string('contact_person_no')->nullable();
           $table->unsignedBigInteger('country_id')->nullable();

           $table->unsignedBigInteger('user_id')->index();
           $table->unsignedBigInteger('created_by')->nullable()->index();
           $table->unsignedBigInteger('updated_by')->nullable()->index();
           $table->timestamps();

           // Foreign keys
           $table->foreign('country_id', 'principals_country_id_fk')->references('id')->on('countries')->onDelete('set null');
           $table->foreign('user_id', 'principals_user_id_fk')->references('id')->on('users')->onDelete('cascade');
           $table->foreign('created_by', 'principals_created_by_fk')->references('id')->on('users')->onDelete('set null');
           $table->foreign('updated_by', 'principals_updated_by_fk')->references('id')->on('users')->onDelete('set null');


       });
   }


   public function down(): void
   {
       Schema::dropIfExists('principals');
   }
};
