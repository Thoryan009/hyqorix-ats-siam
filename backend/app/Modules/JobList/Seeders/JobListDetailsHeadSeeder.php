<?php

namespace App\Modules\JobList\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\JobList\Models\JobListDetailsHead;
use App\Modules\JobList\Models\JobListDetailsCategory;

class JobListDetailsHeadSeeder extends Seeder
{
     public function run(): void
    {
        $visaCategoryId = JobListDetailsCategory::where('name', 'Visa Processing Fees')->first()->id;
        $recruitmentCategoryId = JobListDetailsCategory::where('name', 'Recruitment Fees')->first()->id;

        $items = [
            [
                'name' => 'Medical Fee',
                'amount' => 20000,
                'amount_usd' => 200,
                'job_list_details_category_id' => $visaCategoryId,
                'created_by' => 1,
            ],
            [
                'name' => 'MOFA Fee',
                'amount' => 30000,
                'amount_usd' => 300,
                'job_list_details_category_id' => $visaCategoryId,
                'created_by' => 1,
            ],
            [
                'name' => 'Visa Submission Fee',
                'amount' => 40000,
                'amount_usd' => 400,
                'job_list_details_category_id' => $visaCategoryId,
                'created_by' => 1,
            ],
            [
                'name' => 'Tasheer Fee',
                'amount' => 1000,
                'amount_usd' => 10,
                'job_list_details_category_id' => $visaCategoryId,
                'created_by' => 1,
            ],
            [
                'name' => 'Takamol Fee',
                'amount' => 20000,
                'amount_usd' => 200,
                'job_list_details_category_id' => $visaCategoryId,
                'created_by' => 1,
            ],
            [
                'name' => 'Certificate Attestation Fee',
                'amount' => 35000,
                'amount_usd' => 350,
                'job_list_details_category_id' => $visaCategoryId,
                'created_by' => 1,
            ],
            [
                'name' => 'Insurance Fee',
                'amount' => 50000,
                'amount_usd' => 500,
                'job_list_details_category_id' => $visaCategoryId,
                'created_by' => 1,
            ],
            [
                'name' => 'Recruitment Service Charge',
                'amount' => 30000,
                'amount_usd' => 300,
                'job_list_details_category_id' => $recruitmentCategoryId,
                'created_by' => 1,
            ],
        ];

        foreach ($items as $item) {
            JobListDetailsHead::create($item);
        }
    }
}
