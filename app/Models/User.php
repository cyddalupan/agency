<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasTenant;

    protected $fillable = [
        'agency_id',
        'employer_id',
        'name',
        'email',
        'password',
        'user_type',
        'status',
    ];

    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }

    public function permissions()
    {
        return $this->hasMany(UserPermission::class);
    }

    public function activities()
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at');
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->user_type === 'super_admin';
    }

    public function canImpersonate(): bool
    {
        return $this->isSuperAdmin();
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('user_type', $type);
    }
}
