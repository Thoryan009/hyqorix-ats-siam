<?php

namespace App\Modules\PassportHandover\Models;

use App\Modules\Auth\Models\User;
use App\Modules\Employee\Models\Employee;
use App\Traits\LogsActivity;
use App\Traits\TracksUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PassportHandover extends Model
{
    use LogsActivity;
    use TracksUser;

    protected $guarded = [];

    protected $casts = [
        'taken_at' => 'date',
        'expected_return_date' => 'date',
        'return_date' => 'date',
        'is_permanent' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PassportHandoverItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function handedOverBy()
    {
        return $this->belongsTo(Employee::class, 'handed_over_by');
    }
}
