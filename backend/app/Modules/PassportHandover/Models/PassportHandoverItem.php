<?php

namespace App\Modules\PassportHandover\Models;

use App\Modules\Application\Models\Application;
use App\Modules\Auth\Models\User;
use App\Modules\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PassportHandoverItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'expected_return_date' => 'date',
        'return_date' => 'date',
        'reject_date' => 'date',
    ];

    public function passportHandover(): BelongsTo
    {
        return $this->belongsTo(PassportHandover::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function handedOverBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'handed_over_by');
    }

    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'collected_by');
    }

    public function collectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by_user_id');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'rejected_by');
    }

    public function rejectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by_user_id');
    }
}
