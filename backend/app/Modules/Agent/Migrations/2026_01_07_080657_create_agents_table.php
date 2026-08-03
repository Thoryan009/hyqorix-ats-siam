<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
   public function up(): void
   {
       Schema::create('agents', function (Blueprint $table) {
           $table->id();
           $table->string('agent_id')->unique();
           $table->string('address');
           $table->string('nid_no');
           $table->unsignedBigInteger('user_id')->index();
           $table->unsignedBigInteger('created_by')->nullable()->index();
           $table->unsignedBigInteger('updated_by')->nullable()->index();
           $table->timestamps();

           // Foreign keys
           $table->foreign('user_id', 'agents_user_id_fk')->references('id')->on('users')->onDelete('cascade');
           $table->foreign('created_by', 'agents_created_by_fk')->references('id')->on('users')->onDelete('set null');
           $table->foreign('updated_by', 'agents_updated_by_fk')->references('id')->on('users')->onDelete('set null');


       });
   }


   public function down(): void
   {
       Schema::dropIfExists('agents');
   }
};
