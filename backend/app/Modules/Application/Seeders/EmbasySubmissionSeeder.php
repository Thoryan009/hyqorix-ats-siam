<?php

namespace App\Modules\Application\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Application\Models\EmbasySubmission;

class EmbasySubmissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $items = [
         [
                'application_id' => 1,
                'religion' => 'muslim',
                'visa_profession_ar' => 'مهندس',
                'visa_profession_en' => 'Engineer',
                'visit_work_for_ar' => 'شركة ABC',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'application_id' => 2,
                'religion' => 'non-muslim',
                'visa_profession_ar' => 'طبيب',
                'visa_profession_en' => 'Doctor',
                'visit_work_for_ar' => 'مستشفى XYZ',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'application_id' => 3,
                'religion' => 'muslim',
                'visa_profession_ar' => 'معلم',
                'visa_profession_en' => 'Teacher',
                'visit_work_for_ar' => 'مدرسة DEF',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'application_id' => 4,
                'religion' => 'non-muslim',
                'visa_profession_ar' => 'محاسب',
                'visa_profession_en' => 'Accountant',
                'visit_work_for_ar' => 'شركة GHI',
                'created_at' => $now,
                'updated_at' => $now,
            ],
             [
                'application_id' => 5,
                'religion' => 'muslim',
                'visa_profession_ar' => 'مطور برمجيات',
                'visa_profession_en' => 'Software Developer',
                'visit_work_for_ar' => 'شركة JKL',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'application_id' => 6,
                'religion' => 'non-muslim',
                'visa_profession_ar' => 'مصمم جرافيك',
                'visa_profession_en' => 'Graphic Designer',
                'visit_work_for_ar' => 'شركة MNO',
                'created_at' => $now,
                'updated_at' => $now,
            ],
             [
                'application_id' => 7,
                'religion' => 'muslim',
                'visa_profession_ar' => 'مهندس معماري',
                'visa_profession_en' => 'Architect',
                'visit_work_for_ar' => 'شركة PQR',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'application_id' => 8,
                'religion' => 'non-muslim',
                'visa_profession_ar' => 'محامي',
                'visa_profession_en' => 'Lawyer',
                'visit_work_for_ar' => 'شركة STU',
                'created_at' => $now,
                'updated_at' => $now,
            ],
             [
                'application_id' => 9,
                'religion' => 'muslim',
                'visa_profession_ar' => 'ممرض',
                'visa_profession_en' => 'Nurse',
                'visit_work_for_ar' => 'مستشفى VWX',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'application_id' => 10,
                'religion' => 'non-muslim',
                'visa_profession_ar' => 'مدير مشروع',
                'visa_profession_en' => 'Project Manager',
                'visit_work_for_ar' => 'شركة YZ',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'application_id' => 11,
                'religion' => 'muslim',
                'visa_profession_ar' => 'فني كهرباء',
                'visa_profession_en' => 'Electrician',
                'visit_work_for_ar' => 'شركة ABC',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'application_id' => 12,
                'religion' => 'non-muslim',
                'visa_profession_ar' => 'فني ميكانيكا',
                'visa_profession_en' => 'Mechanic',
                'visit_work_for_ar' => 'شركة DEF',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        EmbasySubmission::insert($items);
    }
}
