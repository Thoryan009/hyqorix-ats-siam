<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;    
class Role extends Model
{
        use LogsActivity;

    protected $guarded = [];
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function sync(array $permission_ids){
        $this->permissions()->sync($permission_ids);
    }
}
