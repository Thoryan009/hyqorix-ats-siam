<?php

namespace App\Modules\Parties\Seeders;

use App\Modules\Parties\Models\PartyType;
use Illuminate\Database\Seeder;

class PartyTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'Client', 'name' => 'Client', 'sort_order' => 1, 'source_module' => 'client'],
            ['code' => 'Principal', 'name' => 'Principal', 'sort_order' => 2, 'source_module' => 'principal'],
            ['code' => 'Agent', 'name' => 'Agent', 'sort_order' => 3, 'source_module' => 'agent'],
            ['code' => 'Candidate', 'name' => 'Candidate', 'sort_order' => 4, 'source_module' => 'application'],
            ['code' => 'Vendor', 'name' => 'Vendor', 'sort_order' => 5, 'source_module' => 'vendor'],
            ['code' => 'Staff', 'name' => 'Staff', 'sort_order' => 6, 'source_module' => 'employee'],
            ['code' => 'Owner', 'name' => 'Owner', 'sort_order' => 7, 'source_module' => null],
        ];

        foreach ($types as $type) {
            PartyType::query()->updateOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'sort_order' => $type['sort_order'],
                    'status' => 'active',
                    'source_module' => $type['source_module'] ?? null,
                ]
            );
        }
    }
}
