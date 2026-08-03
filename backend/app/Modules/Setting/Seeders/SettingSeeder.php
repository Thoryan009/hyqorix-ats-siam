<?php

namespace App\Modules\Setting\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Setting\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        // Define a single setting row

        $setting = [
            'software_name'     => 'AlphaSoft',
            'company_name'      => 'Alpha Corp',
            'company_phone'     => '+1-555-1001',
            'company_email'     => 'contact@alphacorp.com',
            'backup_email'      => 'backup@alphacorp.com',
            'company_address'   => '101 Alpha Street, City, Country',
            'company_logo_path' => 'settings/logo.png',
            'created_by' => 1,
        ];

        Setting::create($setting);
    }
}
