<?php

namespace App\Modules\Journals\Models;

use App\Modules\Auth\Models\User;
use App\Modules\Parties\Models\Party;
use App\Traits\LogsActivity;
use App\Traits\TracksUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Journal extends Model
{
    use LogsActivity;
    use TracksUser;

    public const STATUSES = ['draft', 'pending_approval', 'posted'];

    public const COST_TYPES = [
        'general',
        'direct_cost',
        'operating_expense',
        'recruitment_revenue',
        'sales_return_refund',
        'asset',
        'liability',
    ];

    protected $table = 'journals';

    protected $guarded = [];

    protected $casts = [
        'voucher_date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class)->orderBy('sort_order')->orderBy('id');
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function transactionType(): BelongsTo
    {
        return $this->belongsTo(JournalTransactionType::class, 'transaction_type', 'code');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getActivityIdentifier(): string
    {
        return $this->voucher_no ?: "#{$this->id}";
    }
}
