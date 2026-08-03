<?php

namespace App\Modules\Principal\Models;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TracksUser;
use App\Modules\Application\Models\Application;
use App\Modules\JobList\Models\JobList;
use App\Modules\Country\Models\Country;
use App\Traits\LogsActivity;
class Principal extends Model
{
        use LogsActivity;
    use TracksUser;
    // protected $guarded = [];
    protected $guarded = [];

     public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobLists()
    {
        return $this->hasMany(JobList::class);
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
