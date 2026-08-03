<?php

namespace App\Modules\Auth\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Modules\Agent\Models\Agent;
use App\Modules\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Modules\Auth\Models\User as UserModel;
use App\Modules\Client\Models\Client;
use App\Modules\Principal\Models\Principal;
use App\Traits\TracksUser;
use App\Traits\LogsActivity;
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, TracksUser;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
        use LogsActivity;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'whatsapp_no',
        'type',
        'status'
    ];

     const STATUSES = [
        '1',
        '0'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function client()
    {
        return $this->hasOne(Client::class);
    }

    public function vendor()
    {
        return $this->hasOne(\App\Modules\Vendor\Models\Vendor::class);
    }

    public function agent()
    {
        return $this->hasOne(Agent::class);
    }

     public function principal()
    {
        return $this->hasOne(Principal::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(UserModel::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(UserModel::class, 'updated_by');
    }

      public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function getAllPermissions()
    {
        return collect($this->permissions)
            ->merge(
                $this->roles->flatMap->permissions
            )
            ->pluck('slug')
            ->unique()
            ->values();
    }

    public function hasPermission($permission)
    {
        // direct
        if ($this->permissions->contains('slug', $permission)) {
            return true;
        }

        // via role
        foreach ($this->roles as $role) {
            if ($role->permissions->contains('slug', $permission)) {
                return true;
            }
        }

        return false;
    }
}
