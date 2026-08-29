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

    public const STATUSES = ['draft', 'pending_approval', 'approved', 'posted'];

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

    protected $appends = ['receipt_url', 'receipt_urls'];

    protected $casts = [
        'voucher_date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
    ];

    public function getReceiptPathAttribute($value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return array_values(array_filter($value));
        }

        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            return array_values(array_filter($decoded));
        }

        return [$value];
    }

    public function setReceiptPathAttribute($value): void
    {
        if ($value === null || $value === '' || $value === []) {
            $this->attributes['receipt_path'] = null;

            return;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $this->attributes['receipt_path'] = json_encode(array_values(array_filter($decoded)));

                return;
            }

            $this->attributes['receipt_path'] = json_encode([$value]);

            return;
        }

        if (is_array($value)) {
            $this->attributes['receipt_path'] = json_encode(array_values(array_filter($value)));

            return;
        }

        $this->attributes['receipt_path'] = null;
    }

    public function getReceiptUrlsAttribute(): array
    {
        $paths = $this->receipt_path ?? [];

        return array_map(
            static fn (string $path) => asset('storage/'.$path),
            $paths
        );
    }

    public function getReceiptUrlAttribute(): ?string
    {
        return $this->receipt_urls[0] ?? null;
    }

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
