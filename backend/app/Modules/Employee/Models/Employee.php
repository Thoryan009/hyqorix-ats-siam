<?php

namespace App\Modules\Employee\Models;

use App\Modules\Auth\Models\User;
use App\Modules\Employee\Schemas\EmployeeTableSchema;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TracksUser;
use App\Traits\LogsActivity;



class Employee extends Model
{
        use LogsActivity;
    use TracksUser;

    protected $guarded = [];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }
    public function departments()
    {
        return $this->belongsToMany(Department::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function workOrders()
    {
        return $this->hasMany(\App\Modules\WorkOrder\Models\WorkOrder::class);
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
        return EmployeeTableSchema::columns();
    }

    public static function availableFilters($filters = []): array
    {
        return EmployeeTableSchema::filters($filters);
    }
}
