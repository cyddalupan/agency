<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    public const CATEGORY_HOUSEHOLD = 'household';
    public const CATEGORY_SKILLED = 'skilled';

    protected $fillable = ['name', 'description', 'category'];

    public function scopeHousehold($query)
    {
        return $query->where('category', self::CATEGORY_HOUSEHOLD);
    }

    public function scopeSkilled($query)
    {
        return $query->where(function ($q) {
            $q->where('category', self::CATEGORY_SKILLED)->orWhereNull('category');
        });
    }
}
