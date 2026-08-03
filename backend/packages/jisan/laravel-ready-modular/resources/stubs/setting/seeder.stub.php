<?php

namespace App\Modules\__MODEL__\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\__MODEL__\Models\__MODEL__;

class __MODEL__Seeder extends Seeder
{
    public function run(): void
    {

        $setting = [
            'software_name'     => 'AlphaSoft',
            'company_name'      => 'Alpha Corp',
            'company_phone'     => '+1-555-1001',
            'company_email'     => 'contact@alphacorp.com',
            'company_address'   => '101 Alpha Street, City, Country',
            'company_logo_path' => 'settings/logo.png',
        ];

        __MODEL__::create($setting);
    }
}
