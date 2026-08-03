<?php

namespace App\Modules\Employee\Schemas;

class DepartmentTableSchema
{
    public static function columns(): array
    {
        return [
            [
                'key' => 'name',
                'label' => 'Department',
            ],
        ];
    }

    public static function filters($filters = []): array
    {
        return [
            [
                'key' => 'category_id',
                'label' => 'Category',
                'type' => 'select',
                'options' => $filters['categories'] ?? [],
            ],
        ];
    }
}
