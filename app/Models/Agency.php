<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'subdomain', 'logo', 'settings', 'status', 'address', 'city', 'email', 'contact_person', 'num_branches'];

    protected $casts = [
        'settings' => 'json',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
