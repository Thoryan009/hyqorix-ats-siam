<?php

namespace App\Modules\Finance\Seeders;

use App\Modules\Finance\Models\FinanceBank;
use Illuminate\Database\Seeder;

class FinanceBankSeeder extends Seeder
{
    public function run(): void
    {
        $banks = [
            [
                'bank_name' => 'Dutch-Bangla Bank PLC',
                'swift_code' => 'DBBLBDDH',
                'address' => 'Sena Kalyan Bhaban, 195 Motijheel C/A, Dhaka-1000',
                'branch_name' => 'Motijheel Branch',
                'status' => 'active',
            ],
            [
                'bank_name' => 'Islami Bank Bangladesh PLC',
                'swift_code' => 'IBBLBDDH',
                'address' => 'Islami Bank Tower, 40 Dilkusha C/A, Dhaka-1000',
                'branch_name' => 'Local Office',
                'status' => 'active',
            ],
            [
                'bank_name' => 'Sonali Bank PLC',
                'swift_code' => 'BSONBDDH',
                'address' => '35-42 Motijheel C/A, Dhaka-1000',
                'branch_name' => 'Corporate Branch',
                'status' => 'inactive',
            ],
        ];

        foreach ($banks as $bank) {
            FinanceBank::query()->updateOrCreate(
                ['bank_name' => $bank['bank_name']],
                $bank
            );
        }
    }
}
