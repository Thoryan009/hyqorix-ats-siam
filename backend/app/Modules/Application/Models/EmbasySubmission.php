<?php

namespace App\Modules\Application\Models;

use Illuminate\Database\Eloquent\Model;

class EmbasySubmission extends Model
{
    protected $guarded = [];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
