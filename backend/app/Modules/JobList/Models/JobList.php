<?php

namespace App\Modules\JobList\Models;

use App\Modules\Application\Models\Application;
use App\Modules\Auth\Models\User;
use App\Modules\WorkOrder\Models\WorkOrder;
use App\Modules\Principal\Models\Principal;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TracksUser;
use App\Traits\LogsActivity;

class JobList extends Model
{
    use TracksUser;
    use LogsActivity;

    protected $guarded = [];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function client()
    {
        return $this->belongsTo(\App\Modules\Client\Models\Client::class);
    }

    public function principal()
    {
        return $this->belongsTo(Principal::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function jobListDetails()
    {
        return $this->hasMany(JobListDetail::class);
    }

    // Job model
    public function filteredApplications()
    {
        return $this->hasMany(Application::class);
    }

    public function filteredApplicationsForFinalCount()
    {
        return $this->hasMany(Application::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
