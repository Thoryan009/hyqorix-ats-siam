<?php

namespace App\Modules\Setting\Models;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TracksUser;
use App\Traits\LogsActivity;
class Setting extends Model
{
    use TracksUser;
        use LogsActivity;


    protected $appends = ['company_logo_url', 'login_background_image_url', 'fav_icon_url'];
    protected $guarded = [];

    public function getCompanyLogoUrlAttribute(): ?string
    {
        return $this->company_logo_path ? asset('storage/' . $this->company_logo_path) : null;
    }

    public function getLoginBackgroundImageUrlAttribute(): ?string
    {
        return $this->login_background_image_path ? asset('storage/' . $this->login_background_image_path) : null;
    }

    public function getFavIconUrlAttribute(): ?string
    {
        return $this->fav_icon_path ? asset('storage/' . $this->fav_icon_path) : null;
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
