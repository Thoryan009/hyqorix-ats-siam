<?php

namespace App\Modules\Finance\Models;

use App\Modules\Auth\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceAccountLedgerEntry extends Model
{
    use LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'entry_date' => 'date',
        'dr_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'cr_amount' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'finance_account_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(FinanceAccountTransaction::class, 'finance_account_transaction_id');
    }

    public function typeTransaction(): BelongsTo
    {
        return $this->belongsTo(FinanceAccountTypeTransaction::class, 'finance_account_type_transaction_id');
    }

    public function billEntry(): BelongsTo
    {
        return $this->belongsTo(FinanceBillEntry::class, 'finance_bill_entry_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
