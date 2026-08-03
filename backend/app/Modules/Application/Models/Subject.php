<?php

namespace App\Modules\Application\Models;

use App\Traits\TracksUser;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Auth\Models\User;
use App\Traits\LogsActivity;
class Subject extends Model
{
    protected $guarded = [];
        use LogsActivity;
    use TracksUser;

       public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
