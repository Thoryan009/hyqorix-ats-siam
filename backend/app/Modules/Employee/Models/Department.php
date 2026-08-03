<?php

namespace App\Modules\Employee\Models;
use App\Modules\Employee\Schemas\DepartmentTableSchema;
use App\Modules\Auth\Models\User;


use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $guarded = [];

    public function employees()
    {
        return $this->belongsToMany(Employee::class);
    }

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
        return DepartmentTableSchema::columns();
    }

    public static function availableFilters($filters = []): array
    {
        return DepartmentTableSchema::filters($filters);
    }
}
