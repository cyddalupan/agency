<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = ['agency_id', 'name', 'address', 'contact', 'status'];

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function agents()
    {
        return $this->hasMany(Agent::class);
    }
}
