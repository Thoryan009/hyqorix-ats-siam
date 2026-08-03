<?php
namespace App\Modules\Application\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('application_experiences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id')->index();

            $table->string('company_name')->nullable();
            $table->string('position')->nullable();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->json('responsibilities')->nullable();
            $table->enum('types', ['bd_exp', 'overseas_exp'])->nullable();

            $table->timestamps();

            $table->foreign('application_id', 'exp_application_id_fk')
                ->references('id')
                ->on('applications')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_experiences');
    }
};
