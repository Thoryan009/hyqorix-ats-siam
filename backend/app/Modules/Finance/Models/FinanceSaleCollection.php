<?php

namespace App\Modules\Finance\Models;

use App\Modules\Application\Models\Application;
use App\Modules\Auth\Models\User;
use App\Modules\JobList\Models\JobList;
use App\Traits\LogsActivity;
use App\Traits\TracksUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceSaleCollection extends Model
{
    use LogsActivity;
    use TracksUser;

    protected $guarded = [];

    protected $casts = [
        'sale_price' => 'decimal:2',
        'amount' => 'decimal:2',
        'collection_date' => 'date',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function jobList(): BelongsTo
    {
        return $this->belongsTo(JobList::class, 'job_list_id');
    }

    public function typeTransaction(): BelongsTo
    {
        return $this->belongsTo(FinanceAccountTypeTransaction::class, 'finance_account_type_transaction_id');
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
