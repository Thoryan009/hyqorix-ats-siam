<?php

namespace App\Modules\Journals\Models;

use App\Modules\Auth\Models\User;
use App\Traits\LogsActivity;
use App\Traits\TracksUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalTransactionType extends Model
{
    use LogsActivity;
    use TracksUser;

    protected $table = 'journal_transaction_types';

    protected $guarded = [];

    protected $casts = [
        'subledger_required' => 'boolean',
        'demand_letter_required' => 'boolean',
    ];

    public function journals(): HasMany
    {
        return $this->hasMany(Journal::class, 'transaction_type', 'code');
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
        return $this->code ?: "#{$this->id}";
    }
}
