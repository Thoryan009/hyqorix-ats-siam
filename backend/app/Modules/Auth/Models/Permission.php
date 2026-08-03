<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;
class Permission extends Model
{

        use LogsActivity;

    protected $guarded = [];
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
