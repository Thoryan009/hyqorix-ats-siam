<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('job_lists', function (Blueprint $table) {
            $table->json('payer_json')->nullable()->after('salary');
        });

        DB::table('job_lists')->orderBy('id')->chunkById(100, function ($jobs) {
            foreach ($jobs as $job) {
                $payers = match ($job->payer) {
                    'both' => ['client', 'candidate'],
                    'candidate' => ['candidate'],
                    'client' => ['client'],
                    default => ['client'],
                };

                DB::table('job_lists')
                    ->where('id', $job->id)
                    ->update(['payer_json' => json_encode($payers)]);
            }
        });

        Schema::table('job_lists', function (Blueprint $table) {
            $table->dropColumn('payer');
        });

        Schema::table('job_lists', function (Blueprint $table) {
            $table->json('payer')->nullable()->after('salary');
        });

        DB::table('job_lists')->orderBy('id')->chunkById(100, function ($jobs) {
            foreach ($jobs as $job) {
                DB::table('job_lists')
                    ->where('id', $job->id)
                    ->update(['payer' => $job->payer_json]);
            }
        });

        Schema::table('job_lists', function (Blueprint $table) {
            $table->dropColumn('payer_json');
        });
    }

    public function down(): void
    {
        Schema::table('job_lists', function (Blueprint $table) {
            $table->string('payer_legacy')->nullable()->after('salary');
        });

        DB::table('job_lists')->orderBy('id')->chunkById(100, function ($jobs) {
            foreach ($jobs as $job) {
                $payers = json_decode($job->payer ?? '[]', true) ?: [];
                $legacy = 'client';

                if (in_array('client', $payers, true) && in_array('candidate', $payers, true)) {
                    $legacy = 'both';
                } elseif (in_array('candidate', $payers, true)) {
                    $legacy = 'candidate';
                } elseif (in_array('client', $payers, true)) {
                    $legacy = 'client';
                }

                DB::table('job_lists')
                    ->where('id', $job->id)
                    ->update(['payer_legacy' => $legacy]);
            }
        });

        Schema::table('job_lists', function (Blueprint $table) {
            $table->dropColumn('payer');
        });

        Schema::table('job_lists', function (Blueprint $table) {
            $table->enum('payer', ['candidate', 'client', 'both'])->default('client')->index();
        });

        DB::table('job_lists')->orderBy('id')->chunkById(100, function ($jobs) {
            foreach ($jobs as $job) {
                DB::table('job_lists')
                    ->where('id', $job->id)
                    ->update(['payer' => $job->payer_legacy ?? 'client']);
            }
        });

        Schema::table('job_lists', function (Blueprint $table) {
            $table->dropColumn('payer_legacy');
        });
    }
};
