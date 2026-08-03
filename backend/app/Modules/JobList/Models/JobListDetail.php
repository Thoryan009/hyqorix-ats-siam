<?php

namespace App\Modules\JobList\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Auth\Models\User;
use App\Traits\TracksUser;
use App\Traits\LogsActivity;
class JobListDetail extends Model
{
    protected $guarded = [];
    use TracksUser;
        use LogsActivity;


    public function jobListDetailsHead()
    {
        return $this->belongsTo(JobListDetailsHead::class, 'job_list_details_head_id');
    }

    public function jobList()
    {
        return $this->belongsTo(JobList::class);
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
