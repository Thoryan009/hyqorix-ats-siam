<?php

namespace App\Modules\Parties\Seeders;

use App\Modules\Parties\Models\Party;
use Illuminate\Database\Seeder;

class PartySeeder extends Seeder
{
    public function run(): void
    {
        $parties = [
            [
                'code' => 'CL001',
                'type' => 'Client',
                'name' => 'Gulf Engineering Co.',
                'remarks' => 'Needs manpower; pays recruitment fee',
            ],
            [
                'code' => 'PR001',
                'type' => 'Principal',
                'name' => 'Global HR Consultancy',
                'remarks' => 'Gets work / may arrange visa',
            ],
            [
                'code' => 'AG001',
                'type' => 'Agent',
                'name' => 'Rahman Recruiting Agent',
                'remarks' => 'Brings candidates',
            ],
            [
                'code' => 'CA001',
                'type' => 'Candidate',
                'name' => 'Candidate – Karim',
                'remarks' => 'Direct candidate',
            ],
            [
                'code' => 'VN001',
                'type' => 'Vendor',
                'name' => 'Skyline Air Travels',
                'remarks' => 'Ticket vendor',
            ],
            [
                'code' => 'VN002',
                'type' => 'Vendor',
                'name' => 'City Medical Centre',
                'remarks' => 'Medical provider',
            ],
            [
                'code' => 'ST001',
                'type' => 'Staff',
                'name' => 'Accounts Officer – Hasan',
                'remarks' => 'Takes expense advances',
            ],
        ];

        foreach ($parties as $party) {
            Party::query()->updateOrCreate(
                ['code' => $party['code']],
                array_merge($party, [
                    'opening_debit' => 0,
                    'opening_credit' => 0,
                    'status' => 'active',
                ])
            );
        }
    }
}
