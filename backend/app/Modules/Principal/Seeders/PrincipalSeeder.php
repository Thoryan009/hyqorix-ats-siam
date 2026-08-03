<?php

namespace App\Modules\Principal\Seeders;

use App\Modules\Auth\Models\User;
use App\Modules\Principal\Models\Principal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class PrincipalSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'Principal One',
                'email' => 'principal1@example.com',
                'phone' => '01800000001',
                'whatsapp_no' => '01800000001',
                'principal_id' => 'PR-001',
                'address' => 'Dhaka',
                'country_id' => 2,
                'contact_person_name' => 'John Doe',
                'designation' => 'Manager',
                'contact_person_no' => '01711111111',
            ],
            [
                'name' => 'Principal Two',
                'email' => 'principal2@example.com',
                'phone' => '01800000002',
                'whatsapp_no' => '01800000002',
                'principal_id' => 'PR-002',
                'address' => 'Chittagong',
                'country_id' => 2,
                'contact_person_name' => 'Jane Smith',
                'designation' => 'Director',
                'contact_person_no' => '01722222222',
            ],
            [
                'name' => 'Principal Three',
                'email' => 'principal3@example.com',
                'phone' => '01800000003',
                'whatsapp_no' => '01800000003',
                'principal_id' => 'PR-003',
                'address' => 'Khulna',
                'country_id' => 2,
                'contact_person_name' => 'Michael Lee',
                'designation' => 'CEO',
                'contact_person_no' => '01733333333',
            ],
            [
                'name' => 'Principal Four',
                'email' => 'principal4@example.com',
                'phone' => '01800000004',
                'whatsapp_no' => '01800000004',
                'principal_id' => 'PR-004',
                'address' => 'Rajshahi',
                'country_id' => 3,
                'contact_person_name' => 'Sarah Khan',
                'designation' => 'Coordinator',
                'contact_person_no' => '01744444444',
            ],
            [
                'name' => 'Principal Five',
                'email' => 'principal5@example.com',
                'phone' => '01800000005',
                'whatsapp_no' => '01800000005',
                'principal_id' => 'PR-005',
                'address' => 'Sylhet',
                'country_id' => 4,
                'contact_person_name' => 'David Roy',
                'designation' => 'Supervisor',
                'contact_person_no' => '01755555555',
            ],
            [
                'name' => 'Principal Six',
                'email' => 'principal6@example.com',
                'phone' => '01800000006',
                'whatsapp_no' => '01800000006',
                'principal_id' => 'PR-006',
                'address' => 'Barisal',
                'country_id' => 5,
                'contact_person_name' => 'Emma Watson',
                'designation' => 'Lead',
                'contact_person_no' => '01766666666',
            ],
        ];

        $now = Carbon::now();

        // 1️⃣ Prepare users
        $usersData = collect($items)->map(function ($item) use ($now) {
            return [
                'name'       => $item['name'],
                'email'      => $item['email'],
                'phone'      => $item['phone'],
                'whatsapp_no'=> $item['whatsapp_no'],
                'password'   => Hash::make('12345678'),
                'type'       => 'principal',
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->toArray();

        // 2️⃣ Insert users
        User::insert($usersData);

        // 3️⃣ Get user IDs (same pattern as AgentSeeder)
        $userIds = User::whereIn('email', collect($items)->pluck('email'))
            ->pluck('id')
            ->toArray();

        // 4️⃣ Prepare principals
        $principalsData = collect($items)->map(function ($item, $index) use ($userIds, $now) {
            return [
                'user_id' => $userIds[$index],
                'principal_id' => $item['principal_id'],
                'address' => $item['address'],
                'country_id' => $item['country_id'], // ✅ important
                'contact_person_name' => $item['contact_person_name'],
                'designation' => $item['designation'],
                'contact_person_no' => $item['contact_person_no'],
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->toArray();

        // 5️⃣ Insert principals
        Principal::insert($principalsData);
    }
}