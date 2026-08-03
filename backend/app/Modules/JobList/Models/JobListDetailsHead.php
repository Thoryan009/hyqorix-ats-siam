<?php

namespace App\Modules\JobList\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Auth\Models\User;
use App\Traits\TracksUser;
use App\Traits\LogsActivity;
class JobListDetailsHead extends Model
{
        use TracksUser;
        use LogsActivity;

    protected $guarded = [];

    public function jobListDetailsCategory()
    {
        return $this->belongsTo(JobListDetailsCategory::class, 'job_list_details_category_id');
    }

    public function jobListDetails()
    {
        return $this->hasMany(JobListDetail::class);
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
