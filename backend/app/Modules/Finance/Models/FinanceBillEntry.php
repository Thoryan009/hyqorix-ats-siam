<?php

namespace App\Modules\Finance\Models;

use App\Modules\Application\Models\Application;
use App\Modules\Auth\Models\User;
use App\Modules\JobList\Models\JobList;
use App\Modules\WorkOrder\Models\WorkOrder;
use App\Traits\LogsActivity;
use App\Traits\TracksUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceBillEntry extends Model
{
    use LogsActivity;
    use TracksUser;

    protected $guarded = [];

    protected $appends = ['receipt_url', 'receipt_urls', 'manual_approval_url'];

        protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'is_manual_request' => 'boolean',
        'payment_date' => 'date',
        'requested_at' => 'datetime',
        'manager_approved_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
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

    public function getManualApprovalUrlAttribute(): ?string
    {
        $path = $this->attributes['manual_approval_path'] ?? null;

        return $path ? asset('storage/'.$path) : null;
    }

    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    public function expenseHead(): BelongsTo
    {
        return $this->belongsTo(ExpenseHead::class);
    }

    public function assetAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'asset_account_id');
    }

    public function vendorAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'vendor_account_id');
    }

    public function advanceAdjustmentAssetAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'advance_adjustment_asset_account_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function jobList(): BelongsTo
    {
        return $this->belongsTo(JobList::class);
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_id');
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
