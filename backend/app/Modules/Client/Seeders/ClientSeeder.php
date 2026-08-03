<?php

namespace App\Modules\Client\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Auth\Models\User;
use App\Modules\Client\Models\Client;
use App\Modules\Country\Models\Country;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $countryIds = Country::pluck('id')->toArray();

        $items = [
            ['name' => 'Client One',   'email' => 'client1@example.com',  'phone' => '01700000001', 'whatsapp_no' => '01700000001', 'client_id' => 'CLIENT001'],
            ['name' => 'Client Two',   'email' => 'client2@example.com',  'phone' => '01700000002', 'whatsapp_no' => '01700000002', 'client_id' => 'CLIENT002'],
            ['name' => 'Client Three', 'email' => 'client3@example.com',  'phone' => '01700000003', 'whatsapp_no' => '01700000003', 'client_id' => 'CLIENT003'],
            ['name' => 'Client Four',  'email' => 'client4@example.com',  'phone' => '01700000004', 'whatsapp_no' => '01700000004', 'client_id' => 'CLIENT004'],
            ['name' => 'Client Five',  'email' => 'client5@example.com',  'phone' => '01700000005', 'whatsapp_no' => '01700000005', 'client_id' => 'CLIENT005'],
            ['name' => 'Client Six',   'email' => 'client6@example.com',  'phone' => '01700000006', 'whatsapp_no' => '01700000006', 'client_id' => 'CLIENT006'],
            ['name' => 'Client Seven', 'email' => 'client7@example.com',  'phone' => '01700000007', 'whatsapp_no' => '01700000007', 'client_id' => 'CLIENT007'],
            ['name' => 'Client Eight', 'email' => 'client8@example.com',  'phone' => '01700000008', 'whatsapp_no' => '01700000008', 'client_id' => 'CLIENT008'],
            ['name' => 'Client Nine',  'email' => 'client9@example.com',  'phone' => '01700000009', 'whatsapp_no' => '01700000009', 'client_id' => 'CLIENT009'],
            ['name' => 'Client Ten',   'email' => 'client10@example.com', 'phone' => '01700000010', 'whatsapp_no' => '01700000010', 'client_id' => 'CLIENT010'],
            ['name' => 'Client Eleven', 'email' => 'client11@example.com', 'phone' => '01700000011', 'whatsapp_no' => '01700000011', 'client_id' => 'CLIENT011'],
            ['name' => 'Client Twelve', 'email' => 'client12@example.com', 'phone' => '01700000012', 'whatsapp_no' => '01700000012', 'client_id' => 'CLIENT012'],
        ];

        $now = Carbon::now();

        // 1️⃣ Prepare users for bulk insert
        $usersData = collect($items)->map(function ($item) use ($now) {
            return [
                'name'       => $item['name'],
                'email'      => $item['email'],
                'phone'      => $item['phone'],
                'whatsapp_no' => $item['whatsapp_no'],
                'password'   => Hash::make('12345678'),
                'type'       => 'client',
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->toArray();

        // Bulk insert users
        User::insert($usersData);


        $userIds = User::whereIn('email', collect($items)->pluck('email'))->pluck('id')->toArray();

        // 4️⃣ Prepare clients for bulk insert
        $clientsData = collect($items)->map(function ($item, $index) use ($userIds, $countryIds, $now) {
            return [
                'user_id'    => $userIds[$index],
                'client_id'  => $item['client_id'],
                'country_id' => $countryIds[array_rand($countryIds)],
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->toArray();

        // 5️⃣ Bulk insert clients
        Client::insert($clientsData);
    }
}
