<?php

namespace App\Modules\JobList\Seeders;

use App\Modules\JobList\Models\JobList;
use Illuminate\Database\Seeder;
use App\Modules\JobList\Models\JobListDetail;
use App\Modules\JobList\Models\JobListDetailsHead;

class JobListDetailSeeder extends Seeder
{
     public function run(): void
    {
        $jobListIds = JobList::pluck('id')->toArray();
        $headIds = JobListDetailsHead::pluck('id')->toArray();
        $job004Id = JobList::where('job_code', 'JOB-004')->first()->id;
        $job006Id = JobList::where('job_code', 'JOB-006')->first()->id;
        // Example items
        $items = [
            [
                'job_list_id' => $jobListIds[array_rand($jobListIds)],
                'job_list_details_head_id' => $headIds[array_rand($headIds)],
                'amount' => 10000,
                'amount_usd' => 100,
                'created_by' => 1,
            ],
            [
                'job_list_id' => $jobListIds[array_rand($jobListIds)],
                'job_list_details_head_id' => $headIds[array_rand($headIds)],
                'amount' => 15000,
                'amount_usd' => 150,
                'created_by' => 1,
            ],
            [
                'job_list_id' => $jobListIds[array_rand($jobListIds)],
                'job_list_details_head_id' => $headIds[array_rand($headIds)],
                'amount' => 20000,
                'amount_usd' => 200,
                'created_by' => 1,
            ],
            [
                'job_list_id' => $jobListIds[array_rand($jobListIds)],
                'job_list_details_head_id' => $headIds[array_rand($headIds)],
                'amount' => 25000,
                'amount_usd' => 250,
                'created_by' => 1,
            ],
            // newly seeded work order details for JOB-004 and JOB-006
            [
                'job_list_id' => $job004Id,
                'job_list_details_head_id' => 1,
                'amount' => 30000,
                'amount_usd' => 300,
                'created_by' => 1,
            ],
            [
                'job_list_id' => $job004Id,
                'job_list_details_head_id' => 2,
                'amount' => 35000,
                'amount_usd' => 350,
                'created_by' => 1,
            ],
            [
                'job_list_id' => $job004Id,
                'job_list_details_head_id' => 3,
                'amount' => 40000,
                'amount_usd' => 400,
                'created_by' => 1,
            ],

            [
                'job_list_id' => $job004Id,
                'job_list_details_head_id' => 8,
                'amount' => 50000,
                'amount_usd' => 500,
                'created_by' => 1,
            ],
            // ===
            [
                'job_list_id' => $job006Id,
                'job_list_details_head_id' => 3,
                'amount' => 55000,
                'amount_usd' => 550,
                'created_by' => 1,
            ],
            [
                'job_list_id' => $job006Id,
                'job_list_details_head_id' => 4,
                'amount' => 60000,
                'amount_usd' => 600,
                'created_by' => 1,
            ],
            [
                'job_list_id' => $job006Id,
                'job_list_details_head_id' => 8,
                'amount' => 75000,
                'amount_usd' => 750,
                'created_by' => 1,
            ],

        ];

        foreach ($items as $item) {
            JobListDetail::create($item);
        }
    }
}
