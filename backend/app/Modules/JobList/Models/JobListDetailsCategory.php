<?php

namespace App\Modules\JobList\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Auth\Models\User;
use App\Traits\TracksUser;
use App\Traits\LogsActivity;
class JobListDetailsCategory extends Model
{
        use LogsActivity;
    use TracksUser;
    protected $guarded = [];

    public function jobListDetailsHeads()
    {
        return $this->hasMany(JobListDetailsHead::class);
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
