<?php

namespace App\Modules\Parties\Models;

use App\Modules\Auth\Models\User;
use App\Traits\LogsActivity;
use App\Traits\TracksUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Party extends Model
{
    use LogsActivity;
    use TracksUser;

    protected $table = 'parties';

    protected $guarded = [];

    protected $casts = [
        'opening_debit' => 'decimal:2',
        'opening_credit' => 'decimal:2',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
