<?php

namespace App\Modules\Application\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmbassyListItem extends Model
{
    protected $guarded = [];

    public function embassyList(): BelongsTo
    {
        return $this->belongsTo(EmbassyList::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
