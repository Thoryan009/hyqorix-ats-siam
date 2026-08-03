<?php

namespace App\Modules\Application\Models;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TracksUser;
use App\Traits\LogsActivity;

class Process extends Model
{
    use TracksUser;
        use LogsActivity;


    protected $guarded = [];

    public function applicationProcesses()
    {
        return $this->hasMany(ApplicationProcess::class);
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
