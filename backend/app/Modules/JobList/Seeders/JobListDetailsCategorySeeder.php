<?php

namespace App\Modules\JobList\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\JobList\Models\JobListDetailsCategory;

class JobListDetailsCategorySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Visa Processing Fees', 'created_by' => 1],
            ['name' => 'Recruitment Fees', 'created_by' => 1]
        ];

        foreach ($items as $item) {
            JobListDetailsCategory::create($item);
        }
    }
}
