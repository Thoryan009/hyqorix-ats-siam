<?php


namespace App\Modules\Agent\Seeders;


use App\Modules\Auth\Models\User;
use App\Modules\Agent\Models\Agent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;


class AgentSeeder extends Seeder
{
   public function run(): void
   {
       $items = [
           ['name' => 'Agent One',   'email' => 'agent1@example.com',  'phone' => '01800000001', 'whatsapp_no' => '01800000001', 'agent_id' => 'AGENT001', 'nid_no' => 100000001, 'address' => 'Dhaka', 'manager_name' => 'Manager One'],
           ['name' => 'Agent Two',   'email' => 'agent2@example.com',  'phone' => '01800000002', 'whatsapp_no' => '01800000002', 'agent_id' => 'AGENT002', 'nid_no' => 100000002, 'address' => 'Chittagong', 'manager_name' => 'Manager Two'],
           ['name' => 'Agent Three', 'email' => 'agent3@example.com',  'phone' => '01800000003', 'whatsapp_no' => '01800000003', 'agent_id' => 'AGENT003', 'nid_no' => 100000003, 'address' => 'Khulna', 'manager_name' => 'Manager Three'],
           ['name' => 'Agent Four',  'email' => 'agent4@example.com',  'phone' => '01800000004', 'whatsapp_no' => '01800000004', 'agent_id' => 'AGENT004', 'nid_no' => 100000004, 'address' => 'Rajshahi', 'manager_name' => 'Manager Four'],
           ['name' => 'Agent Five',  'email' => 'agent5@example.com',  'phone' => '01800000005', 'whatsapp_no' => '01800000005', 'agent_id' => 'AGENT005', 'nid_no' => 100000005, 'address' => 'Sylhet', 'manager_name' => 'Manager Five'],
           ['name' => 'Agent Six',   'email' => 'agent6@example.com',  'phone' => '01800000006', 'whatsapp_no' => '01800000006', 'agent_id' => 'AGENT006', 'nid_no' => 100000006, 'address' => 'Barisal', 'manager_name' => 'Manager Six'],
       ];


       $now = Carbon::now();


       // 1️⃣ Prepare users for bulk insert
       $usersData = collect($items)->map(function ($item) use ($now) {
           return [
               'name'       => $item['name'],
               'email'      => $item['email'],
               'phone'      => $item['phone'],
               'whatsapp_no'=> $item['whatsapp_no'],
               'password'   => Hash::make('12345678'),
               'type'       => 'agent',
               'created_by' => 1,
               'created_at' => $now,
               'updated_at' => $now,
           ];
       })->toArray();


       // 2️⃣ Bulk insert users
       User::insert($usersData);


       // 3️⃣ Get inserted user IDs
       $userIds = User::whereIn('email', collect($items)->pluck('email'))
           ->pluck('id')
           ->toArray();


       // 4️⃣ Prepare agents for bulk insert
       $agentsData = collect($items)->map(function ($item, $index) use ($userIds, $now) {
           return [
               'user_id'    => $userIds[$index],
               'agent_id'   => $item['agent_id'],
               'address'    => $item['address'],
               'nid_no'     => $item['nid_no'] ?? null,
               'created_by' => 1,
               'created_at' => $now,
               'updated_at' => $now,
               'manager_name' => $item['manager_name'] ?? null,
           ];
       })->toArray();


       // 5️⃣ Bulk insert agents
       Agent::insert($agentsData);
   }
}
