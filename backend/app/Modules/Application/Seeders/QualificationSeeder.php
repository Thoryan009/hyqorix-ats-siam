<?php

namespace App\Modules\Application\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Application\Models\Qualification;

class QualificationSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $items = [
            ['name' => 'CLASS 5', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'CLASS 6', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'CLASS 7', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'CLASS 8', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'CLASS 9', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'CLASS 10', 'created_at' => $now, 'updated_at' => $now],

            ['name' => 'SSC : Secondary School Certificate', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'HSC : Higher Secondary Certificate', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Dakhil', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Alim', 'created_at' => $now, 'updated_at' => $now],

            ['name' => 'B.A : Bachelor of Arts', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'B.B.A : Bachelor of Business Administration', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'B.Com : Bachelor of Commerce', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'B.S.S : Bachelor of Social Science', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'B.Sc : Bachelor of Science', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'B.Sc Engineer : Bachelor of Engineering Science', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'LL.B : Bachelor of Law', 'created_at' => $now, 'updated_at' => $now],

            ['name' => 'M.A : Masters of Arts', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'M.B.A : Masters of Business Administration', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'M.Com : Masters of Commerce', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'M.S.S : Masters of Social Science', 'created_at' => $now, 'updated_at' => $now],

            ['name' => 'Diploma : Engineering Technology of Certificate', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Diploma in Civil Technology', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Diploma in Electrical', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Diploma in Electrical Engineering', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Diploma in Mechanical Technology', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Diploma in Electronics Technology', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Diploma in Instrumentation and Process Control Technology', 'created_at' => $now, 'updated_at' => $now],

            ['name' => 'Civil Technology : Engineering Technology of Certificate', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Electrical House Wiring : Engineering Technology of Certificate', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Electrician : Engineering Technology of Certificate', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Mechanical Technology : Engineering Technology of Certificate', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Technology : Engineering Technology of Certificate', 'created_at' => $now, 'updated_at' => $now],

            ['name' => 'Fazil', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Kamil', 'created_at' => $now, 'updated_at' => $now],
        ];
        Qualification::insert($items);
    }
}
