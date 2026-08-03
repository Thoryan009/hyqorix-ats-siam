<?php
namespace App\Modules\Application\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\TracksUser;
use App\Traits\LogsActivity;
class ApplicationExperience extends Model

{
    protected $guarded = [];
      use LogsActivity;

    protected $casts = [
        'responsibilities' => 'array',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
