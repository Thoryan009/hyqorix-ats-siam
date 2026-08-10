<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 100)->unique();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
        });

        $now = now();

        DB::table('vendor_types')->insert([
            [
                'name' => 'Ticket',
                'code' => 'ticket',
                'status' => 'active',
                'sort_order' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Legal',
                'code' => 'legal',
                'status' => 'active',
                'sort_order' => 2,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_types');
    }
};
