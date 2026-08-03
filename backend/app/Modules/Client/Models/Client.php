<?php

namespace App\Modules\Client\Models;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TracksUser;
use App\Modules\Application\Models\ClientMail;
use App\Modules\Application\Models\ClientTransaction;
use App\Traits\LogsActivity;
class Client extends Model
{
    use TracksUser;
        use LogsActivity;

    protected $guarded = [];
    protected $appends = ['client_image_url'];
    // protected $fillable = [];

     public function getClientImageUrlAttribute()
    {
        return $this->client_image_path ? asset('storage/' . $this->client_image_path) : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function country()
    {
        return $this->belongsTo(\App\Modules\Country\Models\Country::class);
    }

    public function workOrders()
    {
        return $this->hasMany(\App\Modules\WorkOrder\Models\WorkOrder::class);
    }

    public function jobLists()
    {
        return $this->hasManyThrough(
            \App\Modules\JobList\Models\JobList::class,
            \App\Modules\WorkOrder\Models\WorkOrder::class,
            'client_id',
            'work_order_id',
            'id',
            'id'
        );
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function clientMails()
    {
        return $this->hasMany(ClientMail::class);
    }
    public function clientTransactions()
    {
        return $this->hasMany(ClientTransaction::class);
    }
}
