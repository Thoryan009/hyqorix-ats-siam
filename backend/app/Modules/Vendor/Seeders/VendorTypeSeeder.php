<?php

namespace App\Modules\Vendor\Seeders;

use App\Modules\Vendor\Models\VendorType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class VendorTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $types = [
            ['name' => 'Ticket', 'code' => 'ticket', 'sort_order' => 1],
            ['name' => 'Legal', 'code' => 'legal', 'sort_order' => 2],
        ];

        foreach ($types as $type) {
            VendorType::query()->firstOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'status' => 'active',
                    'sort_order' => $type['sort_order'],
                    'created_by' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
