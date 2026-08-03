<?php

namespace App\Modules\Finance\Seeders;

use App\Modules\Auth\Models\Permission;
use App\Modules\Auth\Models\Role;
use Illuminate\Database\Seeder;

class IncomeTaxPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $actions = ['view', 'create', 'edit', 'delete'];
        $permissionModels = [];

        foreach ($actions as $action) {
            $slug = "income_tax.{$action}";
            $permissionModels[$slug] = Permission::firstOrCreate(
                ['slug' => $slug],
                ['name' => ucfirst($action) . ' Income Tax']
            );
        }

        $admin = Role::query()->where('slug', 'admin')->first();
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching(
                collect($permissionModels)->pluck('id')->all()
            );
        }
    }
}
