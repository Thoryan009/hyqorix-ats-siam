<?php

namespace App\Modules\__MODEL__\Models;

use Illuminate\Database\Eloquent\Model;

class __MODEL__ extends Model
{
    protected $appends = ['company_logo_url'];
    protected $guarded = [];
    public function getCompanyLogoUrlAttribute(): ?string
    {
        return $this->company_logo_path ? asset('storage/' . $this->company_logo_path) : null;
    }
}
