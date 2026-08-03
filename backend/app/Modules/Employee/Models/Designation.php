<?php

namespace App\Modules\Employee\Models;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TracksUser;
use App\Traits\LogsActivity;

class Designation extends Model
{
        use LogsActivity;

    use TracksUser;
    protected $guarded = [];
    public function employees()
    {
        return $this->hasMany(Employee::class);
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
