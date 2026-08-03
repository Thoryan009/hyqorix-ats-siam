<?php

namespace App\Modules\Document\Models;

use App\Modules\Auth\Models\User;
use App\Traits\LogsActivity;
use App\Traits\TracksUser;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use LogsActivity;
    use TracksUser;

    protected $guarded = [];

    protected $appends = ['path_url', 'file_name'];

    public function getPathUrlAttribute(): ?string
    {
        return $this->path ? asset('storage/' . $this->path) : null;
    }

    public function getFileNameAttribute(): ?string
    {
        return $this->path ? basename($this->path) : null;
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
