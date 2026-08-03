<?php

namespace App\Modules\Application\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;
class ClientMail extends Model
{
    protected $guarded = [];
        use LogsActivity;
}
