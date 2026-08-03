<?php

namespace App\Modules\WorkOrder\Models;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TracksUser;
use App\Traits\LogsActivity;
class WorkOrder extends Model
{
        use LogsActivity;
    use TracksUser;

    protected $appends = ['work_order_url'];

     public function getWorkOrderUrlAttribute(): ?string
    {
        return $this->work_order_path ? asset('storage/' . $this->work_order_path) : null;
    }
    protected $guarded = [];

    public function client()
    {
        return $this->belongsTo(\App\Modules\Client\Models\Client::class);
    }
    public function jobLists()
    {
        return $this->hasMany(\App\Modules\JobList\Models\JobList::class);
    }

    public function applications()
    {
        return $this->hasManyThrough(
            \App\Modules\Application\Models\Application::class,
            \App\Modules\JobList\Models\JobList::class,
            'work_order_id',
            'job_list_id',
            'id',
            'id'
        );
    }


    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function employee()
    {
        return $this->belongsTo(\App\Modules\Employee\Models\Employee::class);
    }
}
