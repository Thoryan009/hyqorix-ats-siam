<?php

namespace App\Modules\WorkOrder\Seeders;

use App\Modules\Client\Models\Client;
use App\Modules\Employee\Models\Employee;
use Illuminate\Database\Seeder;
use App\Modules\WorkOrder\Models\WorkOrder;
use Carbon\Carbon;


class WorkOrderSeeder extends Seeder
{
    public function run(): void
    {
        $clientIds = Client::pluck('id')->toArray();
        $employeeIds = Employee::whereHas('user')->pluck('id')->toArray();

        $items = [
            [
                'work_order_id' => 'DL-001',
                'candidates' => 25,
                'end_date' => Carbon::now()->addMonths(3),
                'employee_id' => $employeeIds[array_rand($employeeIds)],
                'client_id' => $clientIds[array_rand($clientIds)],
                'created_by' => 1,
            ],
            [
                'work_order_id' => 'DL-002',
                'candidates' => 15,
                'end_date' => Carbon::now()->addMonths(2),
                'employee_id' => $employeeIds[array_rand($employeeIds)],
                'client_id' => $clientIds[array_rand($clientIds)],
                'created_by' => 1,
            ],
            [
                'work_order_id' => 'DL-003',
                'candidates' => 30,
                'end_date' => Carbon::now()->addMonths(4),
                'employee_id' => $employeeIds[array_rand($employeeIds)],
                'client_id' => $clientIds[array_rand($clientIds)],
                'created_by' => 1,
            ],
            [
                'work_order_id' => 'DL-004',
                'candidates' => 10,
                'end_date' => Carbon::now()->addMonths(1),
                'employee_id' => $employeeIds[array_rand($employeeIds)],
                'client_id' => $clientIds[array_rand($clientIds)],
                'created_by' => 1,
            ],
            [
                'work_order_id' => 'DL-005',
                'candidates' => 50,
                'end_date' => Carbon::now()->addMonths(3),
                'employee_id' => $employeeIds[array_rand($employeeIds)],
                'client_id' => $clientIds[array_rand($clientIds)],
                'created_by' => 1,
            ],
        ];

        foreach ($items as $item) {
            WorkOrder::create($item);
        }
    }
}
