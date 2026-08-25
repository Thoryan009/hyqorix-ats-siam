<?php

namespace App\Modules\Parties\Models;

use App\Modules\Auth\Models\User;
use App\Traits\LogsActivity;
use App\Traits\TracksUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartyType extends Model
{
    use LogsActivity;
    use TracksUser;

    protected $table = 'party_types';

    protected $guarded = [];

    public function parties(): HasMany
    {
        return $this->hasMany(Party::class, 'type', 'code');
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
