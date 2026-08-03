<?php

namespace App\Modules\Country\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Country\Models\Country;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $countries = [
            ['name' => 'India', 'created_by' => 1,      'created_at' => $now, 'updated_at' => $now],
            ['name' => 'USA',    'created_by' => 1,    'created_at' => $now, 'updated_at' => $now],
            ['name' => 'France', 'created_by' => 1,    'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Germany', 'created_by' => 1,   'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Australia', 'created_by' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Canada', 'created_by' => 1,    'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Japan', 'created_by' => 1,     'created_at' => $now, 'updated_at' => $now],
            ['name' => 'China', 'created_by' => 1,     'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Brazil', 'created_by' => 1,    'created_at' => $now, 'updated_at' => $now],
        ];

        Country::insert($countries);
    }
}
