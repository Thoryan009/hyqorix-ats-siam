<?php

namespace App\Modules\Application\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Application\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $items = [
            ['name' => 'Science', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Arts', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Commerce', 'created_at' => $now, 'updated_at' => $now],

            ['name' => 'Computer Science', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Information Technology', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Software Engineering', 'created_at' => $now, 'updated_at' => $now],

            ['name' => 'Electrical', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Mechanical', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Civil', 'created_at' => $now, 'updated_at' => $now],

            ['name' => 'Electronics', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Instrumentation', 'created_at' => $now, 'updated_at' => $now],

            ['name' => 'Business Administration', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Accounting', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Finance', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Management', 'created_at' => $now, 'updated_at' => $now],

            ['name' => 'Economics', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Sociology', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Political Science', 'created_at' => $now, 'updated_at' => $now],

            ['name' => 'Law', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'English', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Mathematics', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Physics', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Chemistry', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Biology', 'created_at' => $now, 'updated_at' => $now],
        ];

        Subject::insert($items);
    }
}
