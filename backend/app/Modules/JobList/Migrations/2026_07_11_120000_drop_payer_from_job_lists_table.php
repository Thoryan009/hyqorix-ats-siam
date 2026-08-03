<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Copy leftover job.payer values onto applications that still lack payment_responsibility.
        if (Schema::hasColumn('job_lists', 'payer') && Schema::hasColumn('applications', 'payment_responsibility')) {
            DB::table('applications')
                ->orderBy('id')
                ->chunkById(200, function ($applications) {
                    foreach ($applications as $application) {
                        $existing = json_decode($application->payment_responsibility ?? '[]', true);
                        if (is_array($existing) && count($existing) > 0) {
                            continue;
                        }

                        $job = DB::table('job_lists')->where('id', $application->job_list_id)->first();
                        if (!$job || $job->payer === null) {
                            continue;
                        }

                        $payers = json_decode($job->payer, true);
                        if (!is_array($payers) || count($payers) === 0) {
                            if (is_string($job->payer) && $job->payer !== '' && $job->payer !== '[]') {
                                $payers = match ($job->payer) {
                                    'both' => ['client', 'candidate'],
                                    'candidate', 'client', 'agent' => [$job->payer],
                                    default => [],
                                };
                            } else {
                                $payers = [];
                            }
                        }

                        if (count($payers) === 0) {
                            continue;
                        }

                        DB::table('applications')
                            ->where('id', $application->id)
                            ->update([
                                'payment_responsibility' => json_encode(array_values(array_unique($payers))),
                            ]);
                    }
                });
        }

        if (Schema::hasColumn('job_lists', 'payer')) {
            Schema::table('job_lists', function (Blueprint $table) {
                $table->dropColumn('payer');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('job_lists', 'payer')) {
            Schema::table('job_lists', function (Blueprint $table) {
                $table->json('payer')->nullable()->after('salary');
            });
        }
    }
};
