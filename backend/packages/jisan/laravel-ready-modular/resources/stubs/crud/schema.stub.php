<?php

namespace App\Modules\__PARENT_MODEL__\Schemas;

class __MODEL__TableSchema
{
    public static function columns(): array
    {
        return [
            [
                'key' => 'name',
                'label' => '__MODEL__',
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
