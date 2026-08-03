<?php

namespace App\Modules\Application\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Application\Models\ApplicationExperience;
class ApplicationExperienceSeeder extends Seeder
{
    public function run(): void
    {
        // You can create some dummy data for testing purposes
        $items = [
            [
                'application_id' => 1, // Make sure this application ID exists in your applications table
                'company_name' => 'Tech Company',
                'position' => 'Software Engineer',
                'from_date' => '2020-01-01',
                'to_date' => '2022-01-01',
                'responsibilities' => ['Developed and maintained web applications.'],
            ],
            [
                'application_id' => 2, // Make sure this application ID exists in your applications table
                'company_name' => 'Another Tech Company',
                'position' => 'Senior Software Engineer',
                'from_date' => '2022-02-01',
                'to_date' => '2024-01-01',
                'responsibilities' => ['Led a team of developers and worked on high-impact projects.'],
            ],
            [
                'application_id' => 1, // Make sure this application ID exists in your applications table
                'company_name' => 'Startup Inc.',
                'position' => 'Intern',
                'from_date' => '2019-06-01',
                'to_date' => '2019-12-31',
                'responsibilities' => ['Assisted in developing a mobile application.'],
            ],
            [
                'application_id' => 3, // Make sure this application ID exists in your applications table
                'company_name' => 'Global Tech',
                'position' => 'Project Manager',
                'from_date' => '2018-03-01',
                'to_date' => '2020-12-31',
                'responsibilities' => ['Managed multiple projects and ensured timely delivery.'],
            ],
            [
                'application_id' => 2, // Make sure this application ID exists in your applications table
                'company_name' => 'Enterprise Solutions',
                'position' => 'Business Analyst',
                'from_date' => '2020-05-01',
                'to_date' => '2021-12-31',
                'responsibilities' => ['Analyzed business requirements and provided solutions to improve processes.'],
            ],
            [
                'application_id' => 3, // Make sure this application ID exists in your applications table
                'company_name' => 'Innovative Tech',
                'position' => 'Data Scientist',
                'from_date' => '2021-01-01',
                'to_date' => '2023-01-01',
                'responsibilities' => ['Worked on data analysis and machine learning projects to derive insights.'],
            ],
            [
                'application_id' => 1, // Make sure this application ID exists in your applications table
                'company_name' => 'Creative Solutions',
                'position' => 'UI/UX Designer',
                'from_date' => '2019-01-01',
                'to_date' => '2020-12-31',
                'responsibilities' => ['Designed user interfaces and improved user experience for various applications.'],
            ],
        ];

        $items = array_map(function ($item) {
            $item['responsibilities'] = json_encode($item['responsibilities']);
            return $item;
        }, $items);

        ApplicationExperience::insert($items);
    }
}
