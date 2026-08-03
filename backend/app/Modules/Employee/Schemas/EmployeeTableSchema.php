<?php

namespace App\Modules\Employee\Schemas;

class EmployeeTableSchema
{
    public static function columns(): array
    {
        return [
            [
                'key' => 'image_url',
                'label' => 'Employee Image',
            ],

            [
                'key' => 'name',
                'label' => 'Name',
            ],

           [
                'key' => 'designation',
                'label' => 'Designation',
            ],

            [
                'key' => 'phone',
                'label' => 'Phone',
            ],
            [
                'key' => 'whatsapp_no',
                'label' => 'Whatsapp No',
            ],

            [
                'key' => 'status_formatted',
                'label' => 'Status',
            ],

        ];
    }

    public static function filters($filters = []): array
    {
        return [
            [
                'key' => 'designation_id',
                'label' => 'Designation',
                'type' => 'select',
                'options' => $filters['designations'] ?? [],
            ],
            [
                'key' => 'role_id',
                'label' => 'Role',
                'type' => 'select',
                'options' => $filters['roles'] ?? [],
            ],
            [
                'key' => 'status',
                'label' => 'Status',
                'type' => 'select',
                'options' => $filters['statuses'] ?? [],
            ],
        ];
    }
}
