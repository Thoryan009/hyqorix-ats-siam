<?php

namespace App\Modules\__MODEL__\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\__MODEL__\Models\User;
use Illuminate\Support\Facades\Hash;


class __MODEL__Seeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Software Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('12345678'),
            'type' => 'super_admin',
        ]);
    }
}
