<?php

namespace App\Modules\Parties\Seeders;

use App\Modules\Parties\Models\PartyType;
use Illuminate\Database\Seeder;

class PartyTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'Client', 'name' => 'Client', 'sort_order' => 1],
            ['code' => 'Principal', 'name' => 'Principal', 'sort_order' => 2],
            ['code' => 'Agent', 'name' => 'Agent', 'sort_order' => 3],
            ['code' => 'Candidate', 'name' => 'Candidate', 'sort_order' => 4],
            ['code' => 'Vendor', 'name' => 'Vendor', 'sort_order' => 5],
            ['code' => 'Staff', 'name' => 'Staff', 'sort_order' => 6],
            ['code' => 'Owner', 'name' => 'Owner', 'sort_order' => 7],
        ];

        foreach ($types as $type) {
            PartyType::query()->updateOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'sort_order' => $type['sort_order'],
                    'status' => 'active',
                ]
            );
        }
    }
}
