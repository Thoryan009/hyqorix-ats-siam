<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('worker_image_path')->nullable();
            $table->string('worker_image_link')->nullable();
            $table->string('sur_name');
            $table->string('given_name');
            $table->string('marital_status')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('sex', ['Male', 'Female', 'Other'])->nullable();
            $table->string('nationality')->nullable();
            $table->string('bd_exp')->nullable();
            $table->string('overseas_exp')->nullable();
            $table->string('language')->nullable();
            $table->string('mobile')->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->string('application_id')->unique()->nullable();
            $table->string('nid_path')->nullable();
            $table->string('nid_link')->nullable();
            $table->string('nid_no')->nullable()->index();
            $table->string('passport_path')->nullable();
            $table->string('passport_link')->nullable();
            $table->date('date_of_issue')->nullable();
            $table->date('date_of_expiry')->nullable();
            $table->string('passport_no')->nullable()->index();
            $table->string('place_of_birth')->nullable();
            $table->string('resume_link')->nullable();
            $table->string('resume_path')->nullable();

            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('address')->nullable();
            $table->string('height')->nullable();
            $table->string('weight')->nullable();

            $table->string('documents_link')->nullable();
            $table->string('documents_path')->nullable();
            $table->string('acknowledgment_link')->nullable();
            $table->string('acknowledgment_path')->nullable();
            $table->string('offer_letter_link')->nullable();
            $table->string('offer_letter_path')->nullable();
            $table->string('qvp_link')->nullable();
            $table->string('qvp_path')->nullable();
            $table->string('svp_link')->nullable();
            $table->string('svp_path')->nullable();

            $table->string('driving_license_no')->nullable();

            $table->string('visa_copy_link')->nullable();
            $table->string('visa_copy_path')->nullable();
            $table->string('immigration_clearance_link')->nullable();
            $table->string('immigration_clearance_path')->nullable();

            $table->text('remarks')->nullable();
            $table->text('summary')->nullable();
            $table->float('discount_amount')->nullable();

            // Foreign keys
            $table->unsignedBigInteger('job_list_id')->index();
            $table->unsignedBigInteger('subject_id')->nullable()->index();
            $table->unsignedBigInteger('qualification_id')->nullable()->index();

            $table->unsignedBigInteger('agent_id')->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable()->index();

            $table->foreign('job_list_id', 'applications_job_list_id_fk')->references('id')->on('job_lists')->onDelete('cascade');
            $table->foreign('subject_id', 'applications_subject_id_fk')->references('id')->on('subjects')->onDelete('set null');
            $table->foreign('qualification_id', 'applications_qualification_id_fk')->references('id')->on('qualifications')->onDelete('set null');
            $table->foreign('agent_id', 'applications_agent_id_fk')->references('id')->on('agents')->onDelete('cascade');
            $table->foreign('created_by', 'applications_created_by_fk')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by', 'applications_updated_by_fk')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
