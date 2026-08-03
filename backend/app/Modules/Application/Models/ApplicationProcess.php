<?php

namespace App\Modules\Application\Models;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TracksUser;
use App\Traits\LogsActivity;
class ApplicationProcess extends Model
{
    use TracksUser;
    use LogsActivity;
    protected $guarded = [];
    protected $casts = [
        'data' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    public function getDurationInDays(): int
    {
        if (!$this->started_at) {
            return 0;
        }
        $end = $this->completed_at ?? now();

        return $this->started_at->diffInDays($end);
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
