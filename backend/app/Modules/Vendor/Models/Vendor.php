<?php

namespace App\Modules\Vendor\Models;

use App\Modules\Auth\Models\User;
use App\Traits\LogsActivity;
use App\Traits\TracksUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vendor extends Model
{
    use LogsActivity;
    use TracksUser;

    protected $guarded = [];

    protected $appends = ['vendor_image_url'];

    public function getVendorImageUrlAttribute(): ?string
    {
        return $this->vendor_image_path ? asset('storage/' . $this->vendor_image_path) : null;
    }

    public function vendorType(): BelongsTo
    {
        return $this->belongsTo(VendorType::class, 'vendor_type', 'code');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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
