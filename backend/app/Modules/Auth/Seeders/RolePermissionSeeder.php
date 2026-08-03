<?php

namespace App\Modules\Auth\Seeders;


use App\Modules\Auth\Models\Permission;
use App\Modules\Auth\Models\Role;
use App\Modules\Auth\Models\User;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Roles
        $roles = [
            'admin' => 'Admin',
            'recruiter' => 'Recruiter',
        ];

        $roleModels = [];

        foreach ($roles as $slug => $name) {
            $roleModels[$slug] = Role::firstOrCreate([
                'name' => $name,
                'slug' => $slug,
            ]);
        }

        // 2. Create Permissions (grouped)
        $permissions = [

            'dashboard' => ['view', 'view_summary', 'demand_letter_summary', 'view_job_status', 'view_candidate_process', 'view_financial_overview', 'view_payment_method', 'view_client_billing', 'view_recent_applications', 'view_recent_transactions', 'view_recent_demand_letters', 'view_upcoming_alerts'],
            'flight_summary' => ['view', 'export'],
            'country' => ['create', 'edit', 'delete', 'view', 'view_summary', 'view_clients'],
            'client' => ['create', 'edit', 'delete', 'view', 'view_summary', 'view_work_orders'],
            'vendor' => ['create', 'edit', 'delete', 'view', 'view_summary'],
            'agent' => ['create', 'edit', 'delete', 'view', 'view_summary', 'view_applications', 'view_performance'],
            'principal' => ['create', 'edit', 'delete', 'view', 'view_summary', 'view_job_lists'],
            'demand_letter' => ['create', 'edit', 'delete', 'view', 'view_summary', 'view_jobs'],
            'job' => ['create', 'edit', 'delete', 'view', 'price_details', 'view_summary', 'view_applicants', 'view_applicant_details', 'view_job_overviews', 'view_system_informations', 'select_principal'],
            'job_detail' => ['create', 'edit', 'delete', 'view'],
            'ats' => ['view', 'update', 'delete'],
            'application' => [
                'create',
                'edit',
                'delete',
                'view',
                'view_hiring_list',
                'view_waiting_list',
                'view_rejected_list',
                'view_application_list',
                'view_short_list',
                'bulk_upload',
                'bulk_status_update',
                'offer_extend',
                'generate_biodata',
                'view_summary',
                'view_personal_information',
                'view_education',
                'view_contact',
                'view_nid_details',
                'view_passport',
                'view_resume',
                'view_application',
                'view_documents',
                'view_system_info',
                'view_transaction_history',
                'view_acknowledgement_document'
            ],
            'visa_processing' => ['edit', 'delete', 'view', 'view_details', 'view_report', 'print_report'],
            'embassy_list' => ['create', 'edit', 'delete', 'view'],
            'passport_handover' => ['create', 'edit', 'delete', 'view', 'collect'],
            'document' => ['create', 'edit', 'delete', 'view', 'download'],
            'application_process' => ['create', 'edit', 'delete', 'view'],
            'process' => ['create', 'edit', 'delete', 'view'],
            'subject' => ['create', 'edit', 'delete', 'view'],
            'qualification' => ['create', 'edit', 'delete', 'view'],
            'fee_category' => ['create', 'edit', 'delete', 'view'],
            'fee_head' => ['create', 'edit', 'delete', 'view'],
            'expense_category' => ['create', 'edit', 'delete', 'view'],
            'expense_head' => ['create', 'edit', 'delete', 'view'],
            'income_category' => ['create', 'edit', 'delete', 'view'],
            'income_head' => ['create', 'edit', 'delete', 'view'],
            'finance_bank' => ['create', 'edit', 'delete', 'view'],
            'finance_account' => ['create', 'edit', 'delete', 'view'],
            'income_tax' => ['create', 'edit', 'delete', 'view'],
            'income_statement' => ['view'],
            'trial_balance' => ['view'],
            'gross_profit_report' => ['view'],

            'pos' => ['create', 'edit', 'delete', 'view'],
            'transaction' => ['create', 'edit', 'delete', 'view', 'view_summary', 'view_bill_information', 'view_payment_summary', 'view_payer_information', 'view_application', 'view_system_informations', 'view_transaction_history'],
            'candidate_bill' => ['collect', 'view'],
            'client_bill' => ['send_invoice', 'send_mail', 'cancel_invoice', 'collect_invoice', 'view'],
            'employee' => ['create', 'edit', 'delete', 'view', 'view_summary', 'view_performance'],
            'department' => ['create', 'edit', 'delete', 'view', 'view_summary', 'view_employees'],
            'designation' => ['create', 'edit', 'delete', 'view', 'view_summary', 'view_employees'],
            'ats_report' => ['view'],
            'ats_summary_report' => ['view'],
            'transaction_report' => ['view'],
            'application_report' => ['view'],
            'process_expiry_report' => ['view'],
            'role' => ['create', 'edit', 'delete', 'view'],
            'permission' => ['create', 'edit', 'delete', 'view'],
            'setting' => ['edit', 'view', 'basic', 'password', 'backup', 'embassy', 'document_expiry'],
            'activity' => ['view'],
            'user' => ['create', 'edit', 'delete'],
        ];

        $permissionModels = [];

        foreach ($permissions as $module => $actions) {
            foreach ($actions as $action) {
                $slug = "{$module}.{$action}";

                $permissionModels[$slug] = Permission::firstOrCreate([
                    'name' => ucfirst($action) . ' ' . ucfirst($module),
                    'slug' => $slug,
                ]);
            }
        }


        // Admin → all permissions
        $roleModels['admin']->permissions()->sync(
            collect($permissionModels)->pluck('id')
        );

        // Employee → limited permissions
        $roleModels['recruiter']->permissions()->sync([
            $permissionModels['dashboard.view']->id,
            $permissionModels['dashboard.view_summary']->id,
            $permissionModels['dashboard.demand_letter_summary']->id,
            $permissionModels['dashboard.view_job_status']->id,
            $permissionModels['dashboard.view_candidate_process']->id,
            $permissionModels['dashboard.view_financial_overview']->id,
            $permissionModels['dashboard.view_payment_method']->id,
            $permissionModels['dashboard.view_client_billing']->id,
            $permissionModels['dashboard.view_recent_applications']->id,
            $permissionModels['dashboard.view_recent_transactions']->id,
            $permissionModels['dashboard.view_recent_demand_letters']->id,
            $permissionModels['dashboard.view_upcoming_alerts']->id,
            $permissionModels['country.view']->id,
            $permissionModels['client.view']->id,
            $permissionModels['agent.view']->id,
            $permissionModels['demand_letter.view']->id,
            $permissionModels['job.view']->id,
            $permissionModels['process_expiry_report.view']->id,
        ]);

        // 4. Attach Admin Role to User ID 1
        /** @var User|null $adminUser */
        $adminUser = User::find(1);

        if ($adminUser) {
            $adminUser->roles()->syncWithoutDetaching([
                $roleModels['admin']->id
            ]);
        }
    }
}
