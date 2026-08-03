<?php

namespace App\Modules\Application\Models;

use App\Modules\Client\Models\Client;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TracksUser;
use App\Traits\LogsActivity;
class ClientTransaction extends Model
{
    protected $guarded = [];
        use LogsActivity;


    public function transactions()
    {
        return $this->belongsToMany(Transaction::class, 'bill_no', 'bill_no');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
