<?php

namespace App\Modules\System\Models;

use App\Modules\System\Schemas\ActivityLogTableSchema;
use App\Modules\Auth\Models\User;


use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'context' => 'array',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function tableColumns(): array
    {
        return ActivityLogTableSchema::columns();
    }

    public static function availableFilters($filters = []): array
    {
        return ActivityLogTableSchema::filters($filters);
    }
}
