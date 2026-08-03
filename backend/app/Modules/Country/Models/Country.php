<?php

namespace App\Modules\Country\Models;

use App\Modules\Auth\Models\User;
use App\Modules\Client\Models\Client;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TracksUser;
use App\Traits\LogsActivity;

class Country extends Model
{
    use TracksUser;
    use LogsActivity;
    
    protected $guarded = [];
    protected $appends = ['country_image_url'];

    public function getCountryImageUrlAttribute()
    {
        return $this->country_image_path ? asset('storage/' . $this->country_image_path) : null;
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    function applications()
    {
        return $this->hasMany(\App\Modules\Application\Models\Application::class);
    }

    public function principals()
    {
        return $this->hasMany(\App\Modules\Principal\Models\Principal::class);
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
