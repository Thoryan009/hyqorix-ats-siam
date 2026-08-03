<?php

namespace App\Modules\Finance\Models;

use App\Modules\Auth\Models\User;
use App\Traits\LogsActivity;
use App\Traits\TracksUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceIncomeCollection extends Model
{
    use LogsActivity;
    use TracksUser;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'collection_date' => 'date',
        'candidates' => 'array',
    ];

    public function incomeCategory(): BelongsTo
    {
        return $this->belongsTo(IncomeCategory::class);
    }

    public function incomeHead(): BelongsTo
    {
        return $this->belongsTo(IncomeHead::class);
    }

    public function receiveAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'receive_account_id');
    }

    public function linkedAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'linked_account_id');
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
