<?php

namespace App\Modules\JobList\Seeders;

use App\Modules\WorkOrder\Models\WorkOrder;
use Illuminate\Database\Seeder;
use App\Modules\JobList\Models\JobList;
use App\Modules\Principal\Models\Principal;
use Carbon\Carbon;

class JobListSeeder extends Seeder
{
    public function run(): void
    {
        $workOrderId = WorkOrder::where('work_order_id', 'DL-005')->value('id');

        if (!$workOrderId) {
            $this->command->warn('WorkOrder DL-005 not found.');
            return;
        }
        $workOrderIds = WorkOrder::pluck('id')->toArray();
        $principalIds = Principal::pluck('id')->toArray();
        $items = [
            [
                'job_code' => 'JOB-001',
                'vacancy' => 5,
                'name' => 'Software Developer',
                'experience' => '2 Years',
                'min_age' => 22,
                'max_age' => 35,
                'contract_length' => '2 Years',
                'description' => 'Responsible for developing web applications.',
                'qualification' => 'BSc in Computer Science',
                'language' => 'English',
                'salary' => '50000',
                'price' => 100000.0,
                'client_commission_per_candidate' => 900.0,
                'deadline' => Carbon::now()->addDays(15),
                'interview_date' => Carbon::now()->addDays(10),
                'status' => 'open',
                'work_order_id' => $workOrderIds[array_rand($workOrderIds)],
                'principal_id' => $principalIds[array_rand($principalIds)],
                'created_by' => 1,
            ],
            [
                'job_code' => 'JOB-002',
                'vacancy' => 3,
                'name' => 'Accountant',
                'experience' => '3 Years',
                'min_age' => 25,
                'max_age' => 40,
                'contract_length' => '2 Years',
                'description' => 'Handle company financial records.',
                'qualification' => 'BCom',
                'language' => null,
                'salary' => '40000',
                'price' => 85000.0,
                'client_commission_per_candidate' => 750.0,
                'deadline' => Carbon::now()->addDays(12),
                'interview_date' => Carbon::now()->addDays(10),
                'status' => 'open',
                'work_order_id' => $workOrderIds[array_rand($workOrderIds)],
                'principal_id' => $principalIds[array_rand($principalIds)],
                'created_by' => 1,
            ],
            [
                'job_code' => 'JOB-003',
                'vacancy' => 10,
                'name' => 'Factory Worker',
                'experience' => '1 Year',
                'min_age' => 20,
                'max_age' => 45,
                'contract_length' => '1 Year',
                'description' => 'General factory work.',
                'qualification' => null,
                'language' => 'English',
                'salary' => '25000',
                'price' => 50000.0,
                'client_commission_per_candidate' => 450.0,
                'deadline' => Carbon::now()->addDays(10),
                'interview_date' => Carbon::now()->addDays(5),
                'status' => 'open',
                'work_order_id' => $workOrderIds[array_rand($workOrderIds)],
                'principal_id' => $principalIds[array_rand($principalIds)],
                'created_by' => 1,
            ],
            [
                'job_code' => 'JOB-004',
                'vacancy' => 4,
                'name' => 'Project Manager',
                'experience' => '5 Years',
                'min_age' => 28,
                'max_age' => 45,
                'contract_length' => '3 Years',
                'description' => 'Manage project timelines, resources, and team members.',
                'qualification' => 'MBA or relevant experience',
                'language' => 'English',
                'salary' => '80000',
                'price' => 150000.0,
                'client_commission_per_candidate' => 1300.0,
                'deadline' => Carbon::now()->addDays(20),
                'interview_date' => Carbon::now()->addDays(15),
                'status' => 'open',
                'work_order_id' => 4,
                'principal_id' => $principalIds[array_rand($principalIds)],
                'created_by' => 1,
            ],
            [
                'job_code' => 'JOB-005',
                'vacancy' => 2,
                'name' => 'UI/UX Designer',
                'experience' => '3 Years',
                'min_age' => 23,
                'max_age' => 35,
                'contract_length' => '1 Year',
                'description' => 'Design user interfaces and improve user experience.',
                'qualification' => 'BDes or related degree',
                'language' => 'English',
                'salary' => '55000',
                'price' => 90000.0,
                'client_commission_per_candidate' => 800.0,
                'deadline' => Carbon::now()->addDays(15),
                'interview_date' => Carbon::now()->addDays(10),
                'status' => 'closed',
                'work_order_id' => $workOrderIds[array_rand($workOrderIds)],
                'principal_id' => $principalIds[array_rand($principalIds)],
                'created_by' => 1,
            ],

            // newly seeded
            ['job_code' => 'JOB-006', 'vacancy' => 3, 'name' => 'Backend Developer', 'experience' => '4 Years', 'min_age' => 25, 'max_age' => 38, 'contract_length' => '2 Years', 'description' => 'Develop REST APIs and maintain backend services.', 'qualification' => 'BSc in CSE or related field', 'language' => 'English', 'salary' => '70000', 'price' => 90000.0, 'client_commission_per_candidate' => 800.0, 'deadline' => Carbon::now()->addDays(25), 'interview_date' => Carbon::now()->addDays(18), 'status' => 'open', 'work_order_id' => $workOrderId, 'principal_id' => $principalIds[array_rand($principalIds)], 'created_by' => 1],

            ['job_code' => 'JOB-007', 'vacancy' => 2, 'name' => 'Frontend Developer', 'experience' => '3 Years', 'min_age' => 23, 'max_age' => 35, 'contract_length' => '1 Year', 'description' => 'Build responsive UI using Vue.js or React.', 'qualification' => 'BSc or Diploma in IT', 'language' => 'English', 'salary' => '65000', 'price' => 85000.0, 'client_commission_per_candidate' => 750.0, 'deadline' => Carbon::now()->addDays(20), 'interview_date' => Carbon::now()->addDays(14), 'status' => 'open', 'work_order_id' => $workOrderId, 'principal_id' => $principalIds[array_rand($principalIds)], 'created_by' => 1],

            ['job_code' => 'JOB-008', 'vacancy' => 1, 'name' => 'System Administrator', 'experience' => '5 Years', 'min_age' => 28, 'max_age' => 45, 'contract_length' => '3 Years', 'description' => 'Maintain servers, security, and system uptime.', 'qualification' => 'BSc or relevant certification', 'language' => 'English', 'salary' => '80000', 'price' => 95000.0, 'client_commission_per_candidate' => 850.0, 'deadline' => Carbon::now()->addDays(30), 'interview_date' => Carbon::now()->addDays(22), 'status' => 'open', 'work_order_id' => $workOrderId, 'principal_id' => $principalIds[array_rand($principalIds)], 'created_by' => 1],
        ];

        foreach ($items as $item) {
            JobList::create($item);
        }
    }
}
