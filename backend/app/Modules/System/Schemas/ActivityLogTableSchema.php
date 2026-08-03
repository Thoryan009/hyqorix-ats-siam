<?php

namespace App\Modules\System\Schemas;

class ActivityLogTableSchema
{
    public static function columns(): array
    {
        return [
            [
                'key' => 'user_name',
                'label' => 'User',
            ],
            [
                'key' => 'action',
                'label' => 'Action',
            ],
            [
                'key' => 'description',
                'label' => 'Description',
            ],
            [
                'key' => 'context',
                'label' => 'Changes',
            ],
            [
                'key' => 'created_at',
                'label' => 'Created At',
            ],
        ];
    }

    public static function filters($filters = []): array
    {
        return [
            [
                'key' => 'user_id',
                'label' => 'User',
                'type' => 'select',
                'options' => $filters['users'] ?? [],
            ],
        ];
    }
}
