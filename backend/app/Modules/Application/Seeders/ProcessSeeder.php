<?php

namespace App\Modules\Application\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Application\Models\Process;

class ProcessSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // process 1
            [
                'name'     => 'offer_extended',
                'duration' => 2,
                'validity' => 5,
                'notify_before' => 1,
                'created_by' => 1,
            ],
            // process 2
            [
                'name'     => 'visa_authorization',
                'duration' => 2,
                'validity' => 5,
                'notify_before' => 1,
                'created_by' => 1,
            ],
            // process 3
            [
                'name'     => 'medical_test',
                'duration' => 4,
                'validity' => 5,
                'notify_before' => 1,
                'created_by' => 1,
            ],
            // process 4
            [
                'name'     => 'police_clearance',
                'duration' => 14,
                'validity' => 30,
                'notify_before' => 10,
                'created_by' => 1,
            ],
            // process 5
            [
                'name'     => 'trade_test',
                'duration' => 7,
                'validity' => 10,
                'notify_before' => 2,
                'created_by' => 1,
            ],
            // process 6
            [
                'name'     => 'biometric_enrollment',
                'duration' => 2,
                'validity' => 5,
                'notify_before' => 1,
                'created_by' => 1,
            ],
            // process 7
            [
                'name'     => 'embassy_submission',
                'duration' => 2,
                'validity' => 5,
                'notify_before' => 1,
                'created_by' => 1,
            ],
            // process 8
            [
                'name'     => 'bmet_training',
                'duration' => 3,
                'validity' => 15,
                'notify_before' => 12,
                'created_by' => 1,
            ],
            // process 9
            [
                'name'     => 'bmet_biometric_enrollment',
                'duration' => 2,
                'validity' => 52,
                'notify_before' => 11,
                'created_by' => 1,
            ],
            // process 10
            [
                'name'     => 'immigration_clearance',
                'duration' => 2,
                'validity' => 25,
                'notify_before' => 11,
                'created_by' => 1,
            ],
            // process 11
            [
                'name'     => 'pta_request',
                'duration' => 2,
                'validity' => 5,
                'notify_before' => 1,
                'created_by' => 1,
            ],
            // process 12
            [
                'name'     => 'tra_process',
                'duration' => 2,
                'validity' => 5,
                'notify_before' => 1,
                'created_by' => 1,
            ],
            // process 13
            [
                'name'     => 'on_boarding',
                'duration' => 2,
                'validity' => 5,
                'notify_before' => 1,
                'created_by' => 1,
            ],
        ];

        foreach ($items as $item) {
            Process::create($item);
        }
    }
}
