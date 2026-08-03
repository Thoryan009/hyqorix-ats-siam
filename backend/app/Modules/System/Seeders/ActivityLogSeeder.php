<?php

namespace App\Modules\System\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\System\Models\ActivityLog;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $items = [
            [
                'user_id' => 1,
                'action' => 'login',
                'description' => 'John logged into the system',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => 2,
                'action' => 'created',
                'description' => "Siam created Client 'ABC Company' (status: active)",
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => 1,
                'action' => 'updated',
                'description' => "John updated Invoice '#123' (amount: 5000, status: draft)",
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => 3,
                'action' => 'deleted',
                'description' => "Admin deleted Project 'Website Redesign' (status: completed)",
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        ActivityLog::insert($items);
    }
}
