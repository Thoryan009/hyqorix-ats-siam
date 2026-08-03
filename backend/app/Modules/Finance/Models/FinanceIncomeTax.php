<?php

namespace App\Modules\Finance\Models;

use App\Modules\Auth\Models\User;
use App\Traits\LogsActivity;
use App\Traits\TracksUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceIncomeTax extends Model
{
    use LogsActivity;
    use TracksUser;

    protected $guarded = [];

    protected $casts = [
        'year' => 'integer',
        'tax_amount' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'net_profit' => 'decimal:2',
        'payment_date' => 'date',
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
