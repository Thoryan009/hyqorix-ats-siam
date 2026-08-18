<?php

namespace App\Modules\Finance\Models;

use App\Modules\Auth\Models\User;
use App\Traits\LogsActivity;
use App\Traits\TracksUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceAccountTypeTransaction extends Model
{
    use LogsActivity;
    use TracksUser;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::created(function (self $transaction): void {
            if (!empty($transaction->transaction_no)) {
                return;
            }

            $transaction->updateQuietly([
                'transaction_no' => self::formatTransactionNo((int) $transaction->id),
            ]);
        });
    }

    public static function formatTransactionNo(int $id): string
    {
        return sprintf('TXN-%06d', $id);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'account_id');
    }

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'from_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'to_account_id');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(FinanceAccountLedgerEntry::class, 'finance_account_type_transaction_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
