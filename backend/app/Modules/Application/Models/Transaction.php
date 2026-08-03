<?php

namespace App\Modules\Application\Models;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TracksUser;
use App\Traits\LogsActivity;
class Transaction extends Model
{
        use LogsActivity;
    use TracksUser;
    protected $guarded = [];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function clientTransaction(){
        return $this->hasOne(ClientTransaction::class, 'bill_no', 'bill_no');
    }

    public function billTransactions()
    {
        return $this->hasMany(self::class, 'bill_no', 'bill_no');
    }
    public function invoiceBillTransactions()
    {
       return $this->hasMany(self::class, 'bill_no', 'bill_no')
                ->where('status', '!=', 'bill-generated');
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
