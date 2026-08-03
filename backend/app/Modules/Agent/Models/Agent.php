<?php

namespace App\Modules\Agent\Models;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TracksUser;
use App\Modules\Application\Models\Application;
use App\Traits\LogsActivity;

class Agent extends Model
{
    use TracksUser;
      use LogsActivity;
    protected $guarded = [];
    protected $appends = ['agent_image_url'];
    // protected $fillable = [];

    public function applications()
    {
        return $this->hasMany(Application::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getAgentImageUrlAttribute()
    {
        return $this->agent_image_path ? asset('storage/' . $this->agent_image_path) : null;
    }
}
