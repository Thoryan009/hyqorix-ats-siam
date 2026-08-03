<?php

namespace App\Modules\__PARENT_MODEL__\Models;
use App\Modules\__PARENT_MODEL__\Schemas\__MODEL__TableSchema;
use App\Modules\Auth\Models\User;


use Illuminate\Database\Eloquent\Model;

class __MODEL__ extends Model
{
    protected $guarded = [];

    //  public function category()
    // {
    //     return $this->belongsTo(Category::class);
    // }

       public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

     public static function tableColumns(): array
    {
        return __MODEL__TableSchema::columns();
    }

    public static function availableFilters($filters = []): array
    {
        return __MODEL__TableSchema::filters($filters);
    }
}
