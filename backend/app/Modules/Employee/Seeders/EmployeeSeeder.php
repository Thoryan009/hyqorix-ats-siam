<?php

namespace App\Modules\Employee\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Auth\Models\User;
use App\Modules\Employee\Models\Employee;
use App\Modules\Employee\Models\Designation;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $items = [
              [
                'name' => 'MD Saladduin Sheikh',
                'email' => 'sssakib2484@gmail.com',
                'phone' => '01966847424',
                'whatsapp_no' => '01966847424',
                'designation_id' => 1,
            ],

            [
                'name' => 'Md Shamsut Tabriz',
                'email' => 'mdshamsuttabriz.dev@gmail.com',
                'phone' => '01986884508',
                'whatsapp_no' => '01986884508',
                'designation_id' => 3,
            ],
              [
                'name' => 'Asa Akter Rimy',
                'email' => 'asha86081@gmail.com',
                'phone' => '01706198682',
                'whatsapp_no' => '01706198682',
                'designation_id' => 3,
            ],
              [
                'name' => 'MD Anisul Haque Siam',
                'email' => 'ahshasan009@gmail.com',
                'phone' => '01518927607',
                'whatsapp_no' => '01518927607',
                'designation_id' => 3,
            ],
            [
                'name' => 'Tousif Nirob',
                'email' => 'tnirob40@gmail.com',
                'phone' => '01324166218',
                'whatsapp_no' => '01324166218',
                'designation_id' => 14,

            ],

              [
                'name' => 'Md Mahafuzur Rahaman',
                'email' => 'mdmahafuzur4747@gmail.com',
                'phone' => '01793253675',
                'whatsapp_no' => '01793253675',
                'designation_id' => 10,

            ],
            [
                'name' => 'Shihabun Mobin Jisan',
                'email' => 'shihabunjisan@gmail.com',
                'phone' => '01324166215',
                'whatsapp_no' => '01324166215',
                'designation_id' => 11,
            ],
            [
                'name' => 'Muhammad Shaheen',
                'email' => 'ceo@norqel.com',
                'phone' => '01300643579',
                'whatsapp_no' => '01300643579',
                'designation_id' => 15,
            ],
        ];

        // 2️⃣ Prepare users for bulk insert
        $usersData = collect($items)->map(function ($item) use ($now) {
            return [
                'name'       => $item['name'],
                'email'      => $item['email'],
                'phone'      => $item['phone'],
                'whatsapp_no' => $item['whatsapp_no'],
                'password'   => Hash::make('12345678'),
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->toArray();
        // Bulk insert users
        User::insert($usersData);

        $userMap = User::whereIn('email', collect($items)->pluck('email'))
            ->pluck('id', 'email'); // key = email, value = id

        // Get designation IDs
        $designationIds = Designation::pluck('id')->toArray();

        // Prepare employees for bulk insert
        $employeesData = collect($items)->map(function ($item) use ($userMap, $now) {
            return [
                'user_id'        => $userMap[$item['email']], // ✅ correct mapping
                'designation_id' => $item['designation_id'],
                'send_credentials' => 0,
                'created_by' => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];
        })->toArray();

        Employee::insert($employeesData);


        // Prepare role_user data for bulk insert
        $roleUserData = collect($userMap)->map(function ($userId) use ($now) {
            return [
                'user_id' => $userId,
                'role_id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->values()->toArray();

        // Bulk insert role_user data
        \DB::table('role_user')->insert($roleUserData);
    }
}
