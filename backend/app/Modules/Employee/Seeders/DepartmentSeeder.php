<?php

namespace App\Modules\Employee\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Employee\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $items = [
            ['name' => 'HR', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Finance', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'IT', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Marketing', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Sales', 'created_at' => $now, 'updated_at' => $now],
        ];

        Department::insert($items);
    }
}
