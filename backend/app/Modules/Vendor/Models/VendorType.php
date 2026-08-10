<?php

namespace App\Modules\Vendor\Models;

use App\Modules\Auth\Models\User;
use App\Traits\LogsActivity;
use App\Traits\TracksUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorType extends Model
{
    use LogsActivity;
    use TracksUser;

    protected $guarded = [];

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class, 'vendor_type', 'code');
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
