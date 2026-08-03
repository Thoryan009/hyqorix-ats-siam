<?php

namespace App\Modules\Employee\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Employee\Models\Designation;

class DesignationSeeder extends Seeder
{
    public function run(): void
    {
        $designations = [
            [
                'name' => 'Office Assistant',
                'description' => 'Supports daily administrative and clerical tasks',
                'created_by' => 1,
            ],
             [
                'name' => 'Office Executive',
                'description' => 'Manages office operations and coordination',
                'created_by' => 1,
            ],
            [
                'name' => 'Intern',
                'description' => 'Entry-level trainee gaining practical experience',
                'created_by' => 1,
            ],
            [
                'name' => 'Frontend Developer',
                'description' => 'Builds user interfaces and client-side applications',
                'created_by' => 1,
            ],
            [
                'name' => 'Backend Developer',
                'description' => 'Handles server-side logic, databases, and APIs',
                'created_by' => 1,
            ],
            [
                'name' => 'Full Stack Developer',
                'description' => 'Works on both frontend and backend development',
                'created_by' => 1,
            ],
            [
                'name' => 'Team Lead',
                'description' => 'Leads a team and ensures project delivery',
                'created_by' => 1,
            ],
            [
                'name' => 'Software Engineer',
                'description' => 'Develops and maintains software systems',
                'created_by' => 1,
            ],
            [
                'name' => 'Software Engineer Level 2',
                'description' => 'Mid-level engineer with more responsibility and experience',
                'created_by' => 1,
            ],
            [
                'name' => 'Senior Engineer',
                'description' => 'Experienced engineer leading complex implementations',
                'created_by' => 1,
            ],
            [
                'name' => 'Product Manager',
                'description' => 'Defines product vision and manages product lifecycle',
                'created_by' => 1,
            ],
            [
                'name' => 'Director',
                'description' => 'Oversees multiple teams and strategic planning',
                'created_by' => 1,
            ],
            [
                'name' => 'Chief Technology Officer (CTO)',
                'description' => 'Leads the company’s technology strategy and innovation',
                'created_by' => 1,
            ],
            [
                'name' => 'UI/UX Designer',
                'description' => 'Designs user interfaces and enhances user experience',
                'created_by' => 1,
            ],

            [
                'name' => 'Chief Executive Officer (CEO)',
                'description' => 'Responsible for overall company leadership and decisions',
                'created_by' => 1,
            ],
        ];

        foreach ($designations as $designation) {
            Designation::create($designation);
        }
    }
}
