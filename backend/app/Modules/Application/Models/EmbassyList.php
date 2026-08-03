<?php

namespace App\Modules\Application\Models;

use App\Modules\Auth\Models\User;
use App\Traits\LogsActivity;
use App\Traits\TracksUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmbassyList extends Model
{
    use LogsActivity;
    use TracksUser;

    protected $guarded = [];

    protected $casts = [
        'submit_date' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(EmbassyListItem::class);
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
