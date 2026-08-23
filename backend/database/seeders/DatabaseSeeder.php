<?php

namespace Database\Seeders;

use App\Modules\Agent\Seeders\AgentSeeder;
use App\Modules\Auth\Seeders\RolePermissionSeeder;
use App\Modules\Application\Seeders\ApplicationExperienceSeeder;
use App\Modules\Application\Seeders\ApplicationProcessSeeder;
use App\Modules\Application\Seeders\ApplicationSeeder;
use App\Modules\Application\Seeders\EmbasySubmissionSeeder;
use App\Modules\Application\Seeders\ProcessSeeder;
use App\Modules\Application\Seeders\QualificationSeeder;
use App\Modules\Application\Seeders\SubjectSeeder;
use App\Modules\Application\Seeders\TransactionSeeder;
use App\Modules\Auth\Seeders\AuthSeeder;
use App\Modules\Client\Seeders\ClientSeeder;
use App\Modules\Vendor\Seeders\VendorSeeder;
use App\Modules\Vendor\Seeders\VendorTypeSeeder;
use App\Modules\Country\Seeders\CountrySeeder;
use App\Modules\Employee\Requests\DepartmentRequest;
use App\Modules\Employee\Seeders\DepartmentSeeder;
use App\Modules\Employee\Seeders\DesignationSeeder;
use App\Modules\Employee\Seeders\EmployeeSeeder;
use App\Modules\JobList\Seeders\JobListSeeder;
use App\Modules\Setting\Seeders\SettingSeeder;
use App\Modules\WorkOrder\Seeders\WorkOrderSeeder;
use App\Modules\Accounts\Seeders\ChartOfAccountSeeder;
use App\Modules\Finance\Seeders\ExpenseCategorySeeder;
use App\Modules\Finance\Seeders\IncomeCategorySeeder;
use App\Modules\JobList\Seeders\JobListDetailSeeder;
use App\Modules\JobList\Seeders\JobListDetailsCategorySeeder;
use App\Modules\JobList\Seeders\JobListDetailsHeadSeeder;
use App\Modules\Principal\Seeders\PrincipalSeeder;
use App\Modules\System\Seeders\ActivityLogSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            AuthSeeder::class,
            RolePermissionSeeder::class,
            AgentSeeder::class,
            CountrySeeder::class,
            PrincipalSeeder::class,
            DesignationSeeder::class,
            DepartmentSeeder::class,
            EmployeeSeeder::class,
            CountrySeeder::class,
            ClientSeeder::class,
            VendorTypeSeeder::class,
            VendorSeeder::class,
            WorkOrderSeeder::class,
            JobListSeeder::class,
            SubjectSeeder::class,
            QualificationSeeder::class,
            ApplicationSeeder::class,
            ProcessSeeder::class,
            ApplicationProcessSeeder::class,
            TransactionSeeder::class,
            SettingSeeder::class,
            JobListDetailsCategorySeeder::class,
            JobListDetailsHeadSeeder::class,
            ExpenseCategorySeeder::class,
            IncomeCategorySeeder::class,
            ChartOfAccountSeeder::class,
            JobListDetailSeeder::class,
            ApplicationExperienceSeeder::class,
            ActivityLogSeeder::class,
            EmbasySubmissionSeeder::class,
        ]);
    }
}
