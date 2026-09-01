<?php

namespace App\Modules\Vendor\Seeders;

use App\Modules\Auth\Models\User;
use App\Modules\Vendor\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'organization_name' => 'Skyline Travel Services',
                'email' => 'skyline@example.com',
                'phone' => '+8801711002200',
                'whatsapp_no' => '+8801711002200',
                'vendor_id' => 'VND001',
                'contact_person' => 'Karim Ahmed',
                'address' => 'Dhaka, Bangladesh',
            ],
            [
                'organization_name' => 'Gulf Medical Supplies',
                'email' => 'gulf@example.com',
                'phone' => '+8801822003300',
                'whatsapp_no' => '+8801822003300',
                'vendor_id' => 'VND002',
                'contact_person' => 'Rahim Khan',
                'address' => 'Chittagong, Bangladesh',
            ],
            [
                'organization_name' => 'Orient Visa Support',
                'email' => 'orient@example.com',
                'phone' => '+8801933004400',
                'whatsapp_no' => '+8801933004400',
                'vendor_id' => 'VND003',
                'contact_person' => 'Salam Hossain',
                'address' => 'Sylhet, Bangladesh',
            ],
            [
                'organization_name' => 'Pacific Air Logistics',
                'email' => 'pacific@example.com',
                'phone' => '+8801711005500',
                'whatsapp_no' => '+8801711005500',
                'vendor_id' => 'VND004',
                'contact_person' => 'Nadia Rahman',
                'address' => 'Narayanganj, Bangladesh',
            ],
            [
                'organization_name' => 'Global Ticket House',
                'email' => 'globalticket@example.com',
                'phone' => '+8801822006600',
                'whatsapp_no' => '+8801822006600',
                'vendor_id' => 'VND005',
                'contact_person' => 'Imran Ali',
                'address' => 'Rajshahi, Bangladesh',
            ],
        ];

        $now = Carbon::now();

        $usersData = collect($items)->map(function ($item) use ($now) {
            return [
                'name' => $item['organization_name'],
                'email' => $item['email'],
                'phone' => $item['phone'],
                'whatsapp_no' => $item['whatsapp_no'],
                'password' => Hash::make('12345678'),
                'type' => 'vendor',
                'status' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->toArray();

        User::insert($usersData);

        $userIds = User::whereIn('email', collect($items)->pluck('email'))->pluck('id')->toArray();

        $vendorsData = collect($items)->map(function ($item, $index) use ($userIds, $now) {
            return [
                'user_id' => $userIds[$index],
                'vendor_id' => $item['vendor_id'],
                'organization_name' => $item['organization_name'],
                'contact_person' => $item['contact_person'],
                'address' => $item['address'],
                'send_notification' => true,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->toArray();

        Vendor::insert($vendorsData);
    }
}
